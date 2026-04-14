#!/bin/bash

set -e

platformEnv="$1"
if [[ -z "$platformEnv" ]]; then
  echo "Usage: $0 <platformEnv>"
  exit 1
fi

s3ConfigBucket="devapp-shd-pri-olcsci-build-s3"


token=$(curl -fsS -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 21600")
awsRegion=$(curl -fsS -H "X-aws-ec2-metadata-token: $token" http://169.254.169.254/latest/meta-data/placement/region)

if [[ -z "$token" ]]; then
  echo "Error: Failed to retrieve EC2 metadata token."
  exit 1
fi

if [[ -z "$awsRegion" ]]; then
  echo "Error: Failed to retrieve AWS region from EC2 metadata."
  exit 1
fi

if [[ "$platformEnv" == "prodsupp" ]]; then
  env="PS"
else
  env=$(echo "$platformEnv" | tr '[:lower:]' '[:upper:]')
fi

envFile="$s3ConfigBucket/lambda/OLCS-OLCSDBRefresh-L/$env/env.conf"

# Create a unique temporary output file with restrictive permissions
old_umask=$(umask)
umask 077
outputFile=$(mktemp /tmp/env.decrypt.XXXXXX)
umask "$old_umask"
trap 'rm -f "$outputFile"' EXIT

trap 'rm -f env.conf tmp.blob "$outputFile"' EXIT

export https_proxy="http://proxy.${platformEnv}.olcs.dev-dvsacloud.uk:3128"
export no_proxy="169.254.169.254"


/usr/local/bin/aws s3 cp "s3://$envFile" env.conf


tail -n +2 env.conf | head -n -1 | while read line; do

  rdsUser=$(echo "$line" | awk -F : '{print $1}' | sed 's/[",]//g')
  rdsCipher=$(echo "$line" | awk '{print $2}' | sed 's/[",]//g')
  echo "$rdsCipher" | base64 --decode > tmp.blob 2>/dev/null
  rdsPlain=$(/usr/local/bin/aws kms decrypt --ciphertext-blob fileb://tmp.blob --query Plaintext --output text --region "$awsRegion" | base64 --decode)
  # Escape single quotes for safe inclusion in SQL string literals
  rdsUserEscaped=${rdsUser//\'/\'\'}
  rdsPlainEscaped=${rdsPlain//\'/\'\'}

  if [[ "$rdsUser" != *master* ]]; then
    echo "ALTER USER '$rdsUserEscaped'@'%' IDENTIFIED BY '$rdsPlainEscaped';" >> "$outputFile"
  fi
  rm -f tmp.blob
done

if ! mysql --defaults-file=/usr/local/conf/importanondb.conf -holcsdb-rds."${platformEnv}".olcs.dev-dvsacloud.uk -umaster OLCS_RDS_OLCSDB < "$outputFile"; then
  rm -f "$outputFile"
  exit 1
fi

rm -f "$outputFile"
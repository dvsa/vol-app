#!/bin/bash

# Exit on error, treat unset variables as error, catch errors in pipes
set -euo pipefail

# Logging helper
log_error() {
    echo "ERROR: $1" >&2
}

platformEnv="$1"
if [[ -z "$platformEnv" ]]; then
    log_error "Usage: $0 <platformEnv>"
    exit 1
fi

s3ConfigBucket="devapp-shd-pri-olcsci-build-s3"
awsRegion="${AWS_REGION:-}"

if [[ -z "$awsRegion" ]]; then
    log_error "AWS_REGION environment variable is not set."
    exit 1
fi

# Determine Environment Prefix
if [[ "$platformEnv" == "prodsupp" ]]; then
    env="PS"
else
    env=$(echo "$platformEnv" | tr '[:lower:]' '[:upper:]')
fi

envFile="$s3ConfigBucket/lambda/OLCS-OLCSDBRefresh-L/$env/env.conf"

# Temporary files
conf_file="/tmp/env.conf"
outputFile=$(mktemp /tmp/env.decrypt.XXXXXX)
blob_file="/tmp/tmp.blob"

# Cleanup on exit
trap 'rm -f "$outputFile" "$conf_file" "$blob_file"' EXIT

# Proxy configuration
export https_proxy="http://proxy.${platformEnv}.olcs.dev-dvsacloud.uk:3128"
export no_proxy="169.254.169.254,s3.${awsRegion}.amazonaws.com"

# Download configuration from S3
echo "Downloading s3://$envFile to $conf_file ..."
if ! /usr/local/bin/aws s3 cp "s3://$envFile" "$conf_file" --region "$awsRegion"; then
    log_error "S3 download failed. Check IAM permissions for 's3:GetObject' and 's3:ListBucket'."
    exit 1
fi

echo "Processing credentials from $conf_file ..."
# Parse the file and decrypt each credential
tail -n +2 "$conf_file" | head -n -1 | while read -r line || [[ -n "$line" ]]; do
    rdsUser=$(echo "$line" | awk -F : '{print $1}' | sed 's/[",]//g')
    rdsCipher=$(echo "$line" | awk '{print $2}' | sed 's/[",]//g')
    
    # Decrypt KMS Ciphertext
    echo "$rdsCipher" | base64 -d > "$blob_file"
    
    rdsPlain=$(/usr/local/bin/aws kms decrypt \
        --ciphertext-blob fileb://"$blob_file" \
        --query Plaintext \
        --output text \
        --region "$awsRegion" | base64 -d)

    # Escape single quotes for SQL safety
    rdsUserEscaped=${rdsUser//\'/\'\'}
    rdsPlainEscaped=${rdsPlain//\'/\'\'}

    if [[ "$rdsUser" != *master* ]]; then
        echo "ALTER USER '$rdsUserEscaped'@'%' IDENTIFIED BY '$rdsPlainEscaped';" >> "$outputFile"
    fi
    rm -f "$blob_file"
done

# Update RDS users using the MASTER_RDS_PASSWORD environment variable
echo "Updating RDS users for $platformEnv..."
if ! MYSQL_PWD="${M_DB_PASSWORD}" mysql --no-defaults \
     -h "olcsdb-rds.${platformEnv}.olcs.${DOMAIN}" \
     -u master OLCS_RDS_OLCSDB < "$outputFile"; then
    log_error "MySQL execution failed. Refer to the logs above for the specific SQL error."
    exit 1
fi

echo "Success: Credentials updated."

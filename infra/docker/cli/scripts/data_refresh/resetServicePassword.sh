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

# tmp.blob: The binary data for KMS decryption
conf_file="/tmp/env.conf"

outputFile=$(mktemp /tmp/env.decrypt.XXXXXX)
blob_file="/tmp/tmp.blob"

trap 'rm -f "$outputFile" "$conf_file" "$blob_file"' EXIT

# Include the S3 endpoint in no_proxy to bypass the proxy for S3 traffic
export https_proxy="http://proxy.${platformEnv}.olcs.dev-dvsacloud.uk:3128"
export no_proxy="169.254.169.254,s3.${awsRegion}.amazonaws.com"

# We no longer capture output into a variable so errors go directly to the log
echo "Downloading s3://$envFile to $conf_file ..."
if ! /usr/local/bin/aws s3 cp "s3://$envFile" "$conf_file" --region "$awsRegion"; then
    log_error "S3 download failed. Check IAM permissions for 's3:GetObject' and 's3:ListBucket'."
    exit 1
fi

echo "Processing credentials from $conf_file ..."
# tail/head/while loop is now 'pipefail' safe
tail -n +2 "$conf_file"  | head -n -1 | while read -r line || [[ -n "$line" ]]; do
    rdsUser=$(echo "$line" | awk -F : '{print $1}' | sed 's/[",]//g')
    rdsCipher=$(echo "$line" | awk '{print $2}' | sed 's/[",]//g')
    
    # Decrypt KMS Ciphertext
    echo "$rdsCipher" | base64 -d > "$blob_file"
    
    # If KMS fails, the script will exit here due to set -e
    rdsPlain=$(/usr/local/bin/aws kms decrypt \
        --ciphertext-blob fileb://"$blob_file"  \
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

# Download the MySQL config file from S3
IMPORT_CONF_S3_PATH="s3://devapp-shd-pri-olcsci-build-s3/OLCS-OLCSDBRefresh-L/DEV/importanondb.conf"
LOCAL_IMPORT_CONF_PATH="/usr/local/conf/importanondb.conf"

mkdir -p "$(dirname "$LOCAL_IMPORT_CONF_PATH")"
if ! /usr/local/bin/aws s3 cp "$IMPORT_CONF_S3_PATH" "$LOCAL_IMPORT_CONF_PATH" --region "$AWS_REGION"; then
    log_error "Failed to download importanondb.conf from S3."
    exit 1
fi

# Error redirection (2>) is removed so MySQL sends errors straight to cloudWatch
echo "Updating RDS users for $platformEnv..."
if ! mysql --defaults-file=/usr/local/conf/importanondb.conf \
     -holcsdb-rds."${platformEnv}".olcs.dev-dvsacloud.uk \
     -umaster OLCS_RDS_OLCSDB < "$outputFile"; then
    log_error "MySQL execution failed. Refer to the logs above for the specific SQL error."
    exit 1
fi

echo "Success: Credentials updated."
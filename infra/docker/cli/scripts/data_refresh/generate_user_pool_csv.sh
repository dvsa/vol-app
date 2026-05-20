#!/bin/bash

labelForBranch() {
  case "$1" in
    prodsupp) echo "ps" ;;
    *) echo "$1" ;;
  esac
}

run_on_node="${RUN_ON_NODE:-dev&&api&&olcs}"
platformEnv="${PLATFORM_ENV:-dev}"
Region="${REGION:-eu-west-1}"

environment=$(labelForBranch "$platformEnv")

export http_proxy="http://proxy.ci.olcs.dev-dvsacloud.uk:3128"
export https_proxy="http://proxy.ci.olcs.dev-dvsacloud.uk:3128"
export no_proxy='127.0.0.1,localhost,169.254.169.254,.olcs.dev-dvsacloud.uk'

s3bucket='devapp-shd-pri-olcsci-build-s3'
s3BucketPath='cognito'
scriptdir='/mnt/data/scripts/data_refresh/generate_user_pool'
slackChan='#env-status'
slackFail='#FF9FA1'
slackCompleted='#36A64F'

output_csv="/tmp/db_output.csv"

trap 'rm -rf "$output_csv"' EXIT

echo "[INFO] Using local script directory: $scriptdir"

echo "[INFO] Generating user pool CSV..."
cd "$scriptdir" || { echo "Script directory not found: $scriptdir"; exit 1; }

set -euo pipefail

php_bin="$(command -v php)"
"$php_bin" /mnt/data/scripts/data_refresh/generate_user_pool \
  --mode=nonprod-users \
  --perrole="2" \
  --mycnf=/home/jenkins/.my.cnf \
  --output="$output_csv"

test -f "$output_csv"

upload_path="$s3BucketPath/users-${environment}.txt"
echo "[INFO] Uploading $output_csv to S3 bucket: $s3bucket, path: $upload_path"

if command -v aws >/dev/null; then
  aws s3 cp "$output_csv" "s3://$s3bucket/$upload_path" --region "$Region"
else
  echo "[WARNING] AWS CLI not installed, skipping S3 upload."
fi

slack_message="${platformEnv} User Pool CSV generated"
slack_color="$slackCompleted"

if [[ -n "$SLACK_WEBHOOK_URL" ]]; then
  curl -X POST -H 'Content-type: application/json' \
    --data "{\"channel\": \"$slackChan\", \"attachments\": [{\"color\": \"$slack_color\", \"text\": \"$slack_message\"}]}" \
    "$SLACK_WEBHOOK_URL"
else
  echo "[INFO] Slack message: $slack_message"
fi

echo "[SUCCESS] Completed generate_user_pool_csv.sh"

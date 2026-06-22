#!/bin/bash

if [[ $# -ne 2 ]]; then
  echo "Requires role_arn and external id"
  exit 1
fi

unset AWS_ACCESS_KEY_ID
unset AWS_SECRET_ACCESS_KEY
unset AWS_SESSION_TOKEN

if [[ "${PROXY}" == *":"* ]]; then
  export http_proxy="http://${PROXY}"
  export https_proxy="http://${PROXY}"
else
  export http_proxy="http://${PROXY}:3128"
  export https_proxy="http://${PROXY}:3128"
fi
export NO_PROXY=169.254.169.254,169.254.170.2,localhost,127.0.0.1,.s3.eu-west-1.amazonaws.com,.s3.amazonaws.com,sts.eu-west-1.amazonaws.com,sts.amazonaws.com

region="${AWS_DEFAULT_REGION:-${AWS_REGION:-eu-west-1}}"
ec2_instance_id="ecs-task-$(date +%s)"

error_file="/tmp/sts_error_$$.txt"

creds=$(/usr/local/bin/aws sts assume-role --role-arn "$1" --role-session-name "$ec2_instance_id" --region="$region" --external-id "$2" 2>"$error_file") || {
  echo "ERROR: AWS STS Assume-Role failed!"
  echo "Reason: $(cat "$error_file")"
  rm -f "$error_file"
  exit 7
}
rm -f "$error_file"

export AWS_ACCESS_KEY_ID=$(echo "$creds" | /usr/bin/jq -r '.Credentials.AccessKeyId')
export AWS_SECRET_ACCESS_KEY=$(echo "$creds" | /usr/bin/jq -r '.Credentials.SecretAccessKey')
export AWS_SESSION_TOKEN=$(echo "$creds" | /usr/bin/jq -r '.Credentials.SessionToken')
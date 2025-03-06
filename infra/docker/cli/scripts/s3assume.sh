#!/bin/bash

if [[ $# -ne 2 ]]; then
  echo "Requires role_arn and external id"
  exit 1
fi

unset AWS_ACCESS_KEY_ID
unset AWS_SECRET_ACCESS_KEY
unset AWS_SESSION_TOKEN

export http_proxy=http://${PROXY}:3128
export https_proxy=http://${PROXY}:3128
export NO_PROXY=169.254.169.254
token=$(curl -s -X PUT 'http://169.254.169.254/latest/api/token' -H 'X-aws-ec2-metadata-token-ttl-seconds: 21600')
region=$(curl -s -H "X-aws-ec2-metadata-token: $token" http://169.254.169.254/latest/meta-data/placement/region)
ec2_instance_id=$(curl -s -H "X-aws-ec2-metadata-token: $token" http://169.254.169.254/latest/meta-data/instance-id)

creds=`/usr/local/bin/aws sts assume-role --role-arn $1 --role-session-name $ec2_instance_id --region=$region --external-id $2 | /usr/bin/jq -r '.Credentials'`
export AWS_ACCESS_KEY_ID=`echo $creds | /usr/bin/jq -r '.AccessKeyId'`
export AWS_SECRET_ACCESS_KEY=`echo $creds | /usr/bin/jq -r '.SecretAccessKey'`
export AWS_SESSION_TOKEN=`echo $creds | /usr/bin/jq -r '.SessionToken'`
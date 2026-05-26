#!/bin/bash

set -e

PROXYHOST="proxy.ci.olcs.dev-dvsacloud.uk"
PROXYPORT="3128"
NOPROXY="169.254.169.254"
S3BUCKET="devapp-shd-pri-olcsci-build-s3"
S3BUCKETPATH="cognito"
ASSUME_ROLE="arn:aws:iam::054614622558:role/OLCS-DEVAPPCI-DEVCI-Cognito_Pool_Admin"
PASSPHRASE="56B03196-BB37-440C-AAD2-E0E2278CCF33"
DELETE_BATCH_SIZE="${DELETE_BATCH_SIZE:-200}"

SLACK_CHAN="#env-status"
SLACK_FAIL="#FF9FA1"
SLACK_COMPLETED="#36A64F"
DEFAULT_EMAIL="no@emailaddress.com"

label_for_branch() {
    case "$1" in
        prodsupp) echo "ps" ;;
        *) echo "$1" ;;
    esac
}

send_slack_notification() {
    local channel="$1"
    local color="$2"
    local message="$3"
    echo "SLACK [$color] ($channel): $message"
}

usage() {
    echo "Usage: $0 <environment> <region> [delete_users]"
    echo "  environment: dev, reg, da, qa, demo, prodsupp"
    echo "  region: eu-west-1 or eu-west-2"
    echo "  delete_users: true or false (default true)"
    exit 2
}

assume_role() {
    echo "Assuming AWS role..."
    ROLE_RESPONSE=$(aws sts assume-role \
        --role-arn "$ASSUME_ROLE" \
        --role-session-name jenkins \
        --external-id "$PASSPHRASE" \
        --duration-seconds 3600 \
        --query 'Credentials.[AccessKeyId,SecretAccessKey,SessionToken]' \
        --output text
    )
    read -r AWS_ACCESS_KEY_ID AWS_SECRET_ACCESS_KEY AWS_SESSION_TOKEN <<< "$ROLE_RESPONSE"
    export AWS_ACCESS_KEY_ID AWS_SECRET_ACCESS_KEY AWS_SESSION_TOKEN
}

ENV="$1"
REGION="$2"
DELETE_USERS="${3:-true}"

if [[ -z "$ENV" || -z "$REGION" ]]; then
    usage
fi

if ! [[ "$ENV" =~ ^(dev|reg|da|qa|demo|prodsupp)$ ]]; then
    echo "Invalid environment."; usage
fi

if ! [[ "$REGION" =~ ^(eu-west-1|eu-west-2)$ ]]; then
    echo "Invalid region."; usage
fi

ENVIRONMENT="$(label_for_branch "$ENV")"
UPP_ENVIRONMENT="$(echo "$ENVIRONMENT" | tr '[:lower:]' '[:upper:]')"

export HTTP_PROXY="http://${PROXYHOST}:${PROXYPORT}"
export HTTPS_PROXY="http://${PROXYHOST}:${PROXYPORT}"
export NO_PROXY="$NOPROXY"
export AWS_PAGER=""

USER_FILE="$(mktemp "/tmp/users-${ENVIRONMENT}.txt.XXXXXX")"
DELETE_LIST_FILE="$(mktemp "/tmp/delete-users-${ENVIRONMENT}.txt.XXXXXX")"
trap 'rm -f "$USER_FILE" "$DELETE_LIST_FILE" "${DELETE_LIST_FILE}.batch."*' EXIT

echo "Downloading user file from S3..."
aws s3 cp "s3://${S3BUCKET}/${S3BUCKETPATH}/users-${ENVIRONMENT}.txt" "$USER_FILE" --region "$REGION"

assume_role

echo "Determining Cognito User Pool ID..."
USER_POOL_ID=$(aws cognito-idp list-user-pools \
    --query "UserPools[?Name==\`DVSA-DEVAPP${UPP_ENVIRONMENT}-COGNITO-USERS\`].[Id]" \
    --region "$REGION" \
    --max-results 10 \
    --output text
)

if [[ -z "$USER_POOL_ID" ]]; then
    send_slack_notification "$SLACK_CHAN" "$SLACK_FAIL" "${ENVIRONMENT} User Pool ID could not be determined."
    exit 1
fi

if [[ "$DELETE_USERS" == "true" ]]; then
    echo "Exporting users from user pool..."
    
    /usr/local/bin/delete_users_from_user_pool.py "$USER_POOL_ID" "$REGION" --list-only > "$DELETE_LIST_FILE"

    TOTAL_USERS=$(grep -cve '^\s*$' "$DELETE_LIST_FILE" || true)
    echo "Found $TOTAL_USERS users to delete."

    if [[ "$TOTAL_USERS" -gt 0 ]]; then
        split -l "$DELETE_BATCH_SIZE" "$DELETE_LIST_FILE" "${DELETE_LIST_FILE}.batch."

        for batch_file in "${DELETE_LIST_FILE}.batch."*; do
            [[ -e "$batch_file" ]] || continue
            BATCH_COUNT=$(grep -cve '^\s*$' "$batch_file" || true)
            echo "Deleting batch from $batch_file ($BATCH_COUNT users)..."
            
            if ! /usr/local/bin/delete_users_from_user_pool.py "$USER_POOL_ID" "$REGION" --from-file "$batch_file"; then
                send_slack_notification "$SLACK_CHAN" "$SLACK_FAIL" "${ENVIRONMENT} Failed to delete users."
                exit 1
            fi
        done
    fi

    echo "Users deleted."
fi

echo "Loading users into the user pool..."

if ! /usr/local/bin/load_user_pool.py "$USER_POOL_ID" "$REGION" "$ENVIRONMENT" "DEV/APP/CI-COG-KNOWN-PASSWORD" "$DEFAULT_EMAIL" "$USER_FILE"; then
     send_slack_notification "$SLACK_CHAN" "$SLACK_FAIL" "${ENVIRONMENT} Failed to load users."
     exit 1
fi

send_slack_notification "$SLACK_CHAN" "$SLACK_COMPLETED" "${ENVIRONMENT} User Pool loaded back to default status."
echo "Workflow complete."
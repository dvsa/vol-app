#!/bin/bash

# set -e: exit on error
# set -u: error on unset variables
# set -o pipefail: catch errors in pipes
set -euo pipefail

VALID_PLATFORM_ENVS=("dev" "reg" "da" "qa" "demo" "prodsupp")
VALID_REGIONS=("eu-west-1" "eu-west-2")

# Capture inputs (defaulting to first index if not provided)
platformEnv="${1:-${VALID_PLATFORM_ENVS[0]}}"   
Region="${2:-${VALID_REGIONS[0]}}"             

SLACK_CHAN="#platform_alerts"
SLACK_FAIL="#FF9FA1"
SLACK_COMPLETED="#36A64F"
BUILD_NUMBER="${BUILD_NUMBER:-$$}"

function slack_send() {
    local channel="$1"
    local color="$2"
    local message="$3"
    # Sending to stderr for cloudwatch visibility.
    echo "[SLACK] Channel: $channel, Color: $color, Message: $message" >&2
}

# This trap will catch ANY command that returns a non-zero exit code
trap 'slack_send "$SLACK_CHAN" "$SLACK_FAIL" "The $platformEnv data refresh pipeline has failed"; exit 1' ERR

function reset_service_password() {
    local pEnv="$1"
    local reg="$2"
    echo "--- Step 1: Resetting service passwords ---"
    # Remove the output=$(...) capture so that the script speaks DIRECTLY 
    # to CloudWatch. This ensures real-time logging.
    /mnt/data/scripts/data_refresh/resetServicePassword.sh "$pEnv" "$reg"
}

function generate_user_pool_csv() {
    local pEnv="$1"
    local reg="$2"
    echo "--- Step 2: Generating new user pool CSV ---"
    /mnt/data/scripts/data_refresh/generate_user_pool_csv.sh "$pEnv" "$reg"
}

function load_user_pool() {
    local pEnv="$1"
    local reg="$2"
    echo "--- Step 3: Loading users into pool ---"
    /mnt/data/scripts/data_refresh/load_user_pool.sh "$pEnv" "$reg"
}

slack_send "$SLACK_CHAN" "$SLACK_COMPLETED" "Starting ${platformEnv} data refresh pipeline"

# Because of 'set -e' and the 'ERR' trap, if any of these fail, 
# the script will stop immediately and send the Slack failure alert.

reset_service_password "$platformEnv" "$Region"
generate_user_pool_csv "$platformEnv" "$Region"
load_user_pool "$platformEnv" "$Region"

slack_send "$SLACK_CHAN" "$SLACK_COMPLETED" "The $platformEnv data refresh pipeline has now completed successfully"
echo "Successfully refreshed data!"
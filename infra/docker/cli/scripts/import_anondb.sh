#!/usr/bin/env bash

set -euo pipefail

# ===== CONFIG =====
: "${PROXY:?PROXY not set}"
: "${DOMAIN:?DOMAIN not set}"
: "${PRODTODEV_ASSUME_ROLE_ID:?PRODTODEV_ASSUME_ROLE_ID not set}"
: "${DB_PASSWORD:?DB_PASSWORD not set}"

S3_BUCKET="devapp-olcs-pri-olcs-deploy-s3"
S3_PREFIX="anondata/olcs-db-anon-prod"
DUMP_DIR="/mnt/data/anondump"
DUMP_FILE="olcs-db-anon-latest-import.sql.gz"
RDS_HOST="olcsanondb-rds.${DOMAIN}"
DB_USER="master"

# ===== PROXY =====
export http_proxy="http://${PROXY}:3128"
export https_proxy="http://${PROXY}:3128"
export NO_PROXY="169.254.169.254"

# ===== LOGGING =====
log_msg() {
  echo "$(date +'%Y-%m-%d %H:%M:%S') $*"
}

log_err() {
  echo "$(date +'%Y-%m-%d %H:%M:%S') ERROR: $*" >&2
}

# ===== AWS REGION DETECTION =====
TOKEN=$(curl -s -X PUT "http://169.254.169.254/latest/api/token" \
  -H "X-aws-ec2-metadata-token-ttl-seconds: 21600")

AZ=$(curl -s -H "X-aws-ec2-metadata-token: ${TOKEN}" \
  http://169.254.169.254/latest/meta-data/placement/availability-zone)

REGION="eu-west-1"

export AWS_DEFAULT_REGION="${REGION}"

# ===== ASSUME ROLE =====
log_msg "Assuming cross-account role"
source ./s3assume.sh \
  "arn:aws:iam::054614622558:role/DBAM-ProdToDev-AssumeRole" \
  "${PRODTODEV_ASSUME_ROLE_ID}"

# ===== DOWNLOAD LATEST DUMP =====
log_msg "Fetching latest anonymised DB dump from S3"

mkdir -p "${DUMP_DIR}"

LATEST_KEY=$(
  /usr/local/bin/aws s3 ls "s3://${S3_BUCKET}/${S3_PREFIX}/" --recursive \
    | sort \
    | tail -n 1 \
    | awk '{print $4}'
)

if [[ -z "${LATEST_KEY}" ]]; then
  log_err "No dump file found in S3"
  exit 1
fi

DEST_FILE="${DUMP_DIR}/${DUMP_FILE}"

/usr/local/bin/aws s3 cp \
  "s3://${S3_BUCKET}/${LATEST_KEY}" \
  "${DEST_FILE}" \
  >/dev/null

log_msg "Downloaded: ${LATEST_KEY}"

# ===== IMPORT DATABASE =====
log_msg "Importing dump into ${RDS_HOST}"

export MYSQL_PWD="${DB_PASSWORD}"

zcat "${DEST_FILE}" \
  | sed 's/`OLCS_RDS_OLCSDB`/`OLCS_RDS_OLCSANONDB`/g' \
  | mysql \
      -h "${RDS_HOST}" \
      -u "${DB_USER}"

log_msg "Database import completed"

# ===== CLEANUP =====
rm -f "${DEST_FILE}"

log_msg "Cleanup complete"

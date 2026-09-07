#!/bin/bash

# The purpose of this script is to take a dump of the Aurora read replica database and copy it to S3 for collection by the MI team

# set -e: exit on error
# set -u: error on unset variables
# set -o pipefail: catch errors in pipes
set -euo pipefail

VALID_ENVIRONMENTS=("DEV" "INT" "PP" "PREP" "PROD")
DUMP_DIR="/mnt/data/olcsdump"
DB_NAME="OLCS_RDS_OLCSDB"
DB_USER="olcsbatch"

# Excluded tables — translation/reference data and internal DR tables
EXCLUDED_TABLES="'translation_key_category_link','translation_key','translation_key_location','translation_key_tag_link','DR_EXPECTED_NULLS','translation_key_text','replacement','replacement_tag_link','replacement_category_link','dr_table_counts','dr_expected_deletes'"

log() {
    printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error() {
    echo "ERROR: $1" >&2
}

usage() {
    if [[ -n "${1:-}" ]]; then
        log_error "$1"
    fi
    echo "Usage: $(basename "$0") [ENVIRONMENT]"
    echo ""
    echo "  ENVIRONMENT  One of: ${VALID_ENVIRONMENTS[*]} (default: from \$ENVIRONMENT_NAME)"
    echo ""
    echo "Required environment variables:"
    echo "  READDB_HOST        Aurora read replica hostname"
    echo "  BATCH_DB_PASSWORD  Password for the $DB_USER database user"
    exit 1
}

# Accept environment as first positional argument or fall back to env var
ENVIRONMENT="${1:-${ENVIRONMENT_NAME:-}}"

if [[ -z "$ENVIRONMENT" ]]; then
    usage "ENVIRONMENT must be provided as an argument or via ENVIRONMENT_NAME"
fi

# Validate required environment variables
if [[ -z "${READDB_HOST:-}" ]]; then
    log_error "READDB_HOST environment variable is not set."
    exit 1
fi

if [[ -z "${BATCH_DB_PASSWORD:-}" ]]; then
    log_error "BATCH_DB_PASSWORD environment variable is not set."
    exit 1
fi

case "${ENVIRONMENT}" in
  "DEV"|"INT")
    REPORTS_BUCKET="devapp-olcs-pri-integration-reporting-s3"
    INTEGRATION_BUCKET="devapp-mc-pri-integration-data-s3"
    ;;
  "PP"|"PREP")
    REPORTS_BUCKET="apppp-olcs-pri-integration-reporting-s3"
    INTEGRATION_BUCKET="apppp-mc-pri-integration-data-s3"
    ;;
  "PROD")
    REPORTS_BUCKET="app-olcs-pri-integration-reporting-s3"
    INTEGRATION_BUCKET="app-mc-pri-integration-data-s3"
    ;;
  *)
    usage "Invalid environment '${ENVIRONMENT}'. Must be one of: ${VALID_ENVIRONMENTS[*]}"
    ;;
esac

mysqldump_bin=$(command -v mysqldump)
mysql_bin=$(command -v mysql)

DATE=$(date +"%Y%m%d%H%M%S")
DUMP_FILE="olcsdump-${DATE}.dmp"
MANIFEST_FILE="olcsdump-manifest.txt"
ARCHIVE_FILE="olcsdump-${DATE}.tar.gz"

# Clean up local dump files on exit (success or failure)
trap 'rm -f "${DUMP_DIR}/${DUMP_FILE}" "${DUMP_DIR}/${MANIFEST_FILE}" "${DUMP_DIR}/${ARCHIVE_FILE}"' EXIT

mkdir -p "${DUMP_DIR}"

log "Environment: ${ENVIRONMENT}"
log "Aurora read replica: ${READDB_HOST}"
log "Dump directory: ${DUMP_DIR}"

# Query the list of tables to dump, excluding history and internal tables.
# MYSQL_PWD avoids passing the password on the command line (Aurora-safe).
log "Fetching table list from ${DB_NAME}..."
TABLES=$(MYSQL_PWD="${BATCH_DB_PASSWORD}" "${mysql_bin}" \
    --no-defaults \
    -h "${READDB_HOST}" \
    -u "${DB_USER}" \
    --skip-column-names \
    -e "SELECT TABLE_NAME
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_TYPE = 'BASE TABLE'
          AND TABLE_NAME NOT LIKE '%_hist'
          AND TABLE_NAME NOT IN (${EXCLUDED_TABLES})" \
    "${DB_NAME}")

if [[ -z "$TABLES" ]]; then
    log_error "No tables returned from ${DB_NAME}. Aborting."
    exit 1
fi

# Dump selected tables with a consistent snapshot without locking tables.
log "Dumping database tables to ${DUMP_DIR}/${DUMP_FILE}..."
MYSQL_PWD="${BATCH_DB_PASSWORD}" "${mysqldump_bin}" \
    --no-defaults \
    -h "${READDB_HOST}" \
    -u "${DB_USER}" \
    --skip-triggers \
    --no-create-db \
    --no-tablespaces \
    --single-transaction \
    "${DB_NAME}" ${TABLES} > "${DUMP_DIR}/${DUMP_FILE}"

log "Generating checksum manifest..."
cd "${DUMP_DIR}"
sha256sum "${DUMP_FILE}" > "${MANIFEST_FILE}"

log "Compressing dump and manifest into ${ARCHIVE_FILE}..."
tar czf "${ARCHIVE_FILE}" "${DUMP_FILE}" "${MANIFEST_FILE}"

log "Uploading to reports bucket: s3://${REPORTS_BUCKET}/olcsdump/${ARCHIVE_FILE}..."
aws s3 cp "${DUMP_DIR}/${ARCHIVE_FILE}" "s3://${REPORTS_BUCKET}/olcsdump/${ARCHIVE_FILE}"

log "Uploading to MI integration bucket: s3://${INTEGRATION_BUCKET}/olcsdump/${ARCHIVE_FILE}..."
aws s3 cp "${DUMP_DIR}/${ARCHIVE_FILE}" "s3://${INTEGRATION_BUCKET}/olcsdump/${ARCHIVE_FILE}"

log "SAS MI extract completed successfully."
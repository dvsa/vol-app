#!/bin/bash
#
# Aurora-based database anonymisation script
# ----------------------------------------------------
# Flow:
#   1. Create Aurora cluster snapshot from production read-replica
#   2. Restore a temporary Aurora cluster + instance
#   3. Run anonymisation scripts against restored cluster
#   4. Dump anonymised database and upload to S3
#   5. Clean up temporary cluster and dump files
#

set -euo pipefail

###############################################
# ENVIRONMENT SETUP
###############################################

export http_proxy=http://${PROXY}
export https_proxy=http://${PROXY}
export NO_PROXY=169.254.169.254

nonprod_assume_external_id=${PRODTODEV_ASSUME_ROLE_ID}
prod_reader_cluster=${READDB_ID}
domain=${FULL_DOMAIN}
env=${ENVIRONMENT_NAME}
pass=${BATCH_DB_PASSWORD}
DATE=$(date +"%Y-%m-%d")
TS=$(date +"%Y-%m-%d-%H-%M-%S")
region="eu-west-1"

tmp_cluster_id="olcs-anon-${TS}"
tmp_instance_id="olcs-anon-${TS}-instance"
snapshot_id="olcs-anon-snap-${TS}"

anondb_dump_dir="/mnt/data/anondump"
anondb_tables="template template_test_data translation_key translation_key_text replacement public_holiday fee_type doc_template system_parameter feature_toggle financial_standing_rate"

###############################################
# LOGGING HELPERS
###############################################
log()      { echo "$(date '+%Y-%m-%d %H:%M:%S') $*"; }
log_error(){ echo "$(date '+%Y-%m-%d %H:%M:%S') ERROR: $*" >&2; }


###############################################
# CLEANUP HANDLER
###############################################
cleanup() {
    log "Cleaning up temporary Aurora resources and dump files"

    set +e
    aws rds delete-db-instance \
        --db-instance-identifier "$tmp_instance_id" \
        --skip-final-snapshot --region $region >/dev/null 2>&1

    aws rds delete-db-cluster \
        --db-cluster-identifier "$tmp_cluster_id" \
        --skip-final-snapshot --region $region >/dev/null 2>&1

    rm -f $anondb_dump_dir/olcs-db*anon-$env-$DATE.*
}
trap cleanup EXIT


###############################################
# 1. CREATE SNAPSHOT FROM AURORA PROD READER
###############################################
log "Creating Aurora cluster snapshot: $snapshot_id"
aws rds create-db-cluster-snapshot \
  --db-cluster-snapshot-identifier "$snapshot_id" \
  --db-cluster-identifier "$prod_reader_cluster" \
  --region $region >/dev/null

log "Waiting for snapshot to complete..."
aws rds wait db-cluster-snapshot-available \
  --db-cluster-snapshot-identifier "$snapshot_id" \
  --region $region


###############################################
# 2. RESTORE TEMPORARY AURORA CLUSTER + INSTANCE
###############################################
log "Restoring temporary Aurora cluster: $tmp_cluster_id"

aws rds restore-db-cluster-from-snapshot \
  --db-cluster-identifier "$tmp_cluster_id" \
  --snapshot-identifier "$snapshot_id" \
  --engine aurora-mysql \
  --region $region >/dev/null

log "Creating temporary Aurora instance: $tmp_instance_id"
aws rds create-db-instance \
  --db-instance-identifier "$tmp_instance_id" \
  --db-cluster-identifier "$tmp_cluster_id" \
  --db-instance-class db.r6g.large \
  --engine aurora-mysql \
  --region $region >/dev/null

log "Waiting for cluster to become available..."
aws rds wait db-cluster-available \
  --db-cluster-identifier "$tmp_cluster_id" \
  --region $region

endpoint=$(aws rds describe-db-clusters --db-cluster-identifier "$tmp_cluster_id" --region $region \
           | jq -r '.DBClusters[0].Endpoint')

log "Temporary Aurora is ready at: $endpoint"


###############################################
# 3. RUN ANONYMISATION AGAINST TEMP CLUSTER
###############################################
mkdir -p $anondb_dump_dir/temp
cd niextract/anonymisation_scripts/anon

log "Running anonymisation against restored Aurora cluster"
./run_anonymisation.sh \
   -c "-umaster -h${endpoint} -p${pass}" \
   -d OLCS_RDS_OLCSDB \
   -f ${anondb_dump_dir}/temp \
   -F


###############################################
# 4. DUMP DATA + UPLOAD TO S3
###############################################
log "Dumping anonymised database"
mysqldump -h $endpoint -u master -p${pass} \
  --routines --triggers --set-gtid-purged=OFF \
  --add-drop-database --databases OLCS_RDS_OLCSDB \
  | gzip > $anondb_dump_dir/olcs-db-anon-$env-$DATE.sql.gz

log "Dumping localdev tables"
mysqldump -h $endpoint -u master -p${pass} \
  --skip-triggers --skip-routines --set-gtid-purged=OFF \
  OLCS_RDS_OLCSDB $anondb_tables \
  | sed 's/`OLCS_RDS_OLCSDB`[.]//g' \
  > $anondb_dump_dir/olcs-db-localdev-anon-$env-$DATE.sql

gzip $anondb_dump_dir/olcs-db-localdev-anon-$env-$DATE.sql

log "Dumping table list"
mysql -h $endpoint -u master -p${pass} \
  OLCS_RDS_OLCSDB -e 'SHOW TABLES;' \
  > $anondb_dump_dir/olcs-dbtables-anon-$env-$DATE.txt

log "Assuming role for S3 upload"
source ./s3assume.sh "arn:aws:iam::054614622558:role/DBAM-ProdToDev-AssumeRole" "$nonprod_assume_external_id"

log "Uploading anonymised dumps to S3"
aws s3 cp $anondb_dump_dir s3://devapp-olcs-pri-olcs-deploy-s3/anondata/ \
  --recursive --exclude "*" --include "olcs-db*anon-$env-$DATE.*"


###############################################
# Finished!
###############################################
log "Aurora anonymisation process completed successfully."
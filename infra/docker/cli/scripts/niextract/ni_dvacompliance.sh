#!/bin/bash

set -euo pipefail

###############################################
# ENVIRONMENT
###############################################



: "${PROXY:?PROXY is required}"
: "${DBCLUSTER_ID:?DBCLUSTER_ID is required}"
: "${READDB_HOST:?READDB_HOST is required}"
: "${M_DB_PASSWORD:?M_DB_PASSWORD is required}"
: "${ENVIRONMENT_NAME:?ENVIRONMENT_NAME is required}"

export http_proxy="http://${PROXY}"
export https_proxy="http://${PROXY}"
export NO_PROXY="${NO_PROXY:+${NO_PROXY},}169.254.169.254"

region="eu-west-1"
tmp_cluster_id="ni-extract-$(date +%Y%m%d%H%M%S)-${RANDOM}"
tmp_instance_id="${tmp_cluster_id}-instance"
db_cluster_id=${DBCLUSTER_ID}
snapshot_id="${tmp_cluster_id}-snap"
script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readdb_host=${READDB_HOST}

###############################################
# DEBUG MODE
###############################################

mode=""
while getopts ":hd" opt; do
  case ${opt} in
    d ) echo "Debug mode enabled"; mode="debug" ;;
    h )
      echo "Usage: -d (debug mode)"
      exit 0
      ;;
    \? )
      echo "Invalid option"
      exit 1
      ;;
  esac
done

###############################################
# LOGGING
###############################################
log()  { echo "$(date '+%Y-%m-%d %H:%M:%S') $*"; }
loge() { echo "$(date '+%Y-%m-%d %H:%M:%S') ERROR: $*" >&2; }

###############################################
# CLEANUP
###############################################
cleanup() {
  if [[ "$mode" != "debug" ]]; then
    log "Cleaning up Aurora resources"

    aws rds delete-db-instance \
      --db-instance-identifier "$tmp_instance_id" \
      --skip-final-snapshot --region "$region" >/dev/null 2>&1 || true
    aws rds wait db-instance-deleted \
      --db-instance-identifier "$tmp_instance_id" --region "$region" >/dev/null 2>&1 || true

    aws rds delete-db-cluster \
      --db-cluster-identifier "$tmp_cluster_id" \
      --skip-final-snapshot --region "$region" >/dev/null 2>&1 || true
    aws rds wait db-cluster-deleted \
      --db-cluster-identifier "$tmp_cluster_id" --region "$region" >/dev/null 2>&1 || true

    aws rds delete-db-cluster-snapshot \
      --db-cluster-snapshot-identifier "$snapshot_id" --region "$region" >/dev/null 2>&1 || true
  fi
}
trap cleanup EXIT

###############################################
# 1. SNAPSHOT SOURCE CLUSTER
###############################################
log "Creating snapshot from source cluster"

aws rds create-db-cluster-snapshot \
  --db-cluster-identifier "$db_cluster_id" \
  --db-cluster-snapshot-identifier "$snapshot_id" \
  --region $region >/dev/null

aws rds wait db-cluster-snapshot-available \
  --db-cluster-snapshot-identifier "$snapshot_id" \
  --region $region

###############################################
# 2. FETCH NETWORK CONFIG
###############################################
log "Fetching subnet + security groups"

subnet_group=$(aws rds describe-db-clusters \
  --db-cluster-identifier "$db_cluster_id" \
  --query "DBClusters[0].DBSubnetGroup" \
  --output text --region "$region")
if [[ -z "$subnet_group" || "$subnet_group" == "None" ]]; then
  loge "Failed to determine DB subnet group for cluster $db_cluster_id"
  exit 1
fi

sec_groups=$(aws rds describe-db-clusters \
  --db-cluster-identifier "$db_cluster_id" \
  --query "DBClusters[0].VpcSecurityGroups[].VpcSecurityGroupId" \
  --output text --region "$region")
if [[ -z "$sec_groups" || "$sec_groups" == "None" ]]; then
  loge "Failed to determine VPC security groups for cluster $db_cluster_id"
  exit 1
fi
###############################################
# 3. RESTORE TEMP CLUSTER
###############################################
log "Restoring temporary cluster"

aws rds restore-db-cluster-from-snapshot \
  --db-cluster-identifier "$tmp_cluster_id" \
  --snapshot-identifier "$snapshot_id" \
  --engine aurora-mysql \
  --db-subnet-group-name "$subnet_group" \
  --vpc-security-group-ids $sec_groups \
  --region $region >/dev/null

aws rds wait db-cluster-available \
  --db-cluster-identifier "$tmp_cluster_id" \
  --region $region

###############################################
# 4. CREATE INSTANCE
###############################################
log "Creating cluster instance"

db_instance_class=$(aws rds describe-db-instances \
  --filters "Name=db-cluster-id,Values=${db_cluster_id}" \
  --query "DBInstances[0].DBInstanceClass" \
  --output text --region "$region")
if [[ -z "$db_instance_class" || "$db_instance_class" == "None" ]]; then
  loge "Failed to determine DB instance class for cluster $db_cluster_id"
  exit 1
fi

aws rds create-db-instance \
  --db-instance-identifier "$tmp_instance_id" \
  --db-cluster-identifier "$tmp_cluster_id" \
  --db-instance-class "$db_instance_class" \
  --engine aurora-mysql \
  --region $region >/dev/null

aws rds wait db-instance-available \
  --db-instance-identifier "$tmp_instance_id" \
  --region $region

###############################################
# 5. GET ENDPOINT
###############################################
endpoint=$(aws rds describe-db-clusters \
  --db-cluster-identifier "$tmp_cluster_id" \
  --query "DBClusters[0].Endpoint" \
  --output text --region "$region")

if [[ -z "$endpoint" || "$endpoint" == "None" ]]; then
  loge "Failed to resolve endpoint for temporary cluster $tmp_cluster_id"
  exit 1
fi

log "Cluster ready at: $endpoint"
###############################################
# 6. RUN NI EXTRACT
###############################################
if [[ ! -f "${script_dir}/NI_Extract.sh" || ! -d "${script_dir}/scripts" ]]; then
  loge "Missing NI_Extract.sh or scripts directory in ${script_dir}"
  exit 1
fi

log "Running NI Extract"

cd "${script_dir}"
<% if @env != 'prod' -%>
./NI_Extract.sh \
  -c "-h${endpoint} -umaster" \
  -d readdb_host \
  -A \
  -a $script_dir/anonymisation_scripts/anon \
  -f /tmp/anon\
  -X /tmp/xml
<% else -%>
./NI_Extract.sh \
  -c "-h${endpoint} -umaster" \
  -d readdb_host \
  -X /tmp/xml
  

###############################################
# 7. UPLOAD RESULT TO S3
###############################################
log "Locating extract output"

output_file=$(find /tmp/anon -type f -name "*.tar.gz" | head -n 1)

if [[ -z "$output_file" ]]; then
  loge "No extract file produced"
  exit 1
fi

log "Uploading ${output_file} to S3"

aws s3 cp "$output_file" s3://<%= @dva_report_bucket %>/dvacompliance/

if [ $? -ne 0 ]; then
  loge "Failed to upload extract to S3"
  exit 1
fi

log "Upload successful"

# Cleanup local files (matches original behaviour)
rm -f "$output_file"
rm -f <%= @ni_dvacompliance_dir %>/*.dat || true

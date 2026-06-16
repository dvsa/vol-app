#!/bin/bash

set -euo pipefail

###############################################
# ENVIRONMENT
###############################################

export http_proxy=http://<%= @proxy %>
export https_proxy=http://<%= @proxy %>
export NO_PROXY=169.254.169.254

region="eu-west-1"

db_cluster_id="<%= @ni_dvacompliance_readdb_id %>"
tmp_cluster_id="ni-extract-${RANDOM}"
tmp_instance_id="${tmp_cluster_id}-instance"
snapshot_id="${tmp_cluster_id}-snap"

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
      --skip-final-snapshot --region $region >/dev/null 2>&1 || true

    aws rds delete-db-cluster \
      --db-cluster-identifier "$tmp_cluster_id" \
      --skip-final-snapshot --region $region >/dev/null 2>&1 || true
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
  --output text --region $region)

sec_groups=$(aws rds describe-db-clusters \
  --db-cluster-identifier "$db_cluster_id" \
  --query "DBClusters[0].VpcSecurityGroups[].VpcSecurityGroupId" \
  --output text --region $region)

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

aws rds create-db-instance \
  --db-instance-identifier "$tmp_instance_id" \
  --db-cluster-identifier "$tmp_cluster_id" \
  --db-instance-class db.r6g.large \
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
  --output text --region $region)

log "Cluster ready at: $endpoint"

###############################################
# 6. RUN NI EXTRACT
###############################################
if [ ! -d <%= @dbam_common_script_dir %>/NI_Extract ]; then
  loge "Missing NI_Extract directory"
  exit 1
fi

log "Running NI Extract"

cd <%= @dbam_common_script_dir %>/NI_Extract

<% if @env != 'prod' -%>
./NI_Extract.sh \
  -c "-h${endpoint} -umaster" \
  -d OLCS_RDS_OLCSDB \
  -A \
  -a <%= @dbam_common_script_dir %>/anonymisation_scripts/anon \
  -f <%= @ni_dvacompliance_dir %>/temp \
  -X <%= @ni_dvacompliance_dir %>
<% else -%>
./NI_Extract.sh \
  -c "-h${endpoint} -umaster" \
  -d OLCS_RDS_OLCSDB \
  -X <%= @ni_dvacompliance_dir %>

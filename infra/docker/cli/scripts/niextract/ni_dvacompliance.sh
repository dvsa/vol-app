#!/bin/bash

set -euo pipefail

echoerr() { printf '%s\n' "$*" >&2; }

: "${READDB_HOST:?READDB_HOST is required}"
: "${READDB_ID:?READDB_ID is required}"
: "${ENVIRONMENT_NAME:?ENVIRONMENT_NAME is required (DEV|INT|PREP|PROD)}"
ENVIRONMENT="${ENVIRONMENT_NAME}"

# PROXY is already host:port (includes :3128). Do not append a port here.
if [ -n "${PROXY:-}" ]; then
  export http_proxy="http://${PROXY}"
  export https_proxy="http://${PROXY}"
fi
export NO_PROXY="${NO_PROXY:-169.254.169.254,169.254.170.2,localhost,127.0.0.1}"

# Region: prefer AWS_REGION, else IMDSv2
ec2_region="${AWS_REGION:-}"
if [ -z "$ec2_region" ]; then
  token="$(curl -fsS -X PUT "http://169.254.169.254/latest/api/token" \
    -H "X-aws-ec2-metadata-token-ttl-seconds: 21600" 2>/dev/null || true)"

  if [ -n "$token" ]; then
    ec2_region="$(curl -fsS -H "X-aws-ec2-metadata-token: $token" \
      "http://169.254.169.254/latest/meta-data/placement/region" 2>/dev/null || true)"

    if [ -z "$ec2_region" ]; then
      az="$(curl -fsS -H "X-aws-ec2-metadata-token: $token" \
        "http://169.254.169.254/latest/meta-data/placement/availability-zone" 2>/dev/null || true)"
      [ -n "$az" ] && ec2_region="${az%[a-z]}"
    fi
  fi
fi

if [ -z "$ec2_region" ]; then
  ec2_region="$(/usr/local/bin/aws configure get region 2>/dev/null || true)"
fi

: "${ec2_region:?Could not determine AWS region}"
export AWS_REGION="$ec2_region"
aws_region="$ec2_region"

case "${ENVIRONMENT}" in
  "DEV")  DVA_BUCKET="devapp-olcs-pri-integration-dva-s3"; DVA_PREFIX="dev" ;;
  "INT")  DVA_BUCKET="appnduint-olcs-pri-integration-dva-s3"; DVA_PREFIX="" ;;
  "PREP") DVA_BUCKET="apppp-olcs-pri-integration-dva-s3"; DVA_PREFIX="" ;;
  "PROD") DVA_BUCKET="app-olcs-pri-integration-dva-s3"; DVA_PREFIX="" ;;
  *) echoerr "ERROR: Invalid environment specified"; exit 1 ;;
esac

S3_DEST="s3://${DVA_BUCKET}"
[[ -n "${DVA_PREFIX}" ]] && S3_DEST="${S3_DEST}/${DVA_PREFIX}"
S3_DEST="${S3_DEST}/dvacompliance/"

# Debug mode disables the deletion of the temp Aurora resources
mode=""
while getopts ":hd" opt; do
  case ${opt} in
    d )
      echo "In debug mode"
      mode="debug"
      ;;
    h )
      echo "Usage:"
      echo "    Use -d                      To enable Debug (temp DB will not be dropped)."
      exit 0
      ;;
    \? )
      echo "Invalid Option: -$OPTARG" 1>&2
      echo "    Use -h                      Help usage."
      exit 1
      ;;
  esac
done

#global variable to capture output
aws_cmd_output=
aws_cmd() {
  cmd="$1"
  max_retries="${2:-10}"
  sleep_between="${3:-5}"
  sensitive="${4:-}"

  aws_cmd_output=
  if [ -z "$max_retries" ]; then
    max_retries=10
  fi
  if [ -z "$sleep_between" ]; then
    sleep_between=5
  fi

  cmd_count=1
  while [ $cmd_count -lt $max_retries ];
  do
    if [ -z "$sensitive" ]; then
      echo "Executing [$cmd_count] - [$cmd] .."
    else
      echo "Executing [$cmd_count] - [**cmd_hidden_sensitive**] .."
    fi
    aws_cmd_output=`eval "$cmd"`
    if [ $? -eq 0 ]; then
      echo "Command successful.."
      return 0
    else
      sleep $(($sleep_between * $cmd_count))
      cmd_count=$(($cmd_count + 1))
    fi
  done
  echo "Exhausted all retries [$cmd_count] - giving up.."
  # counter has reached max - fail.
  return 1
}

function log_err {
  t_stamp=$(date +"%Y-%m-%d %H:%M:%S")
  echo "${t_stamp} $@" 1>&2;
}

function log_msg {
  t_stamp=$(date +"%Y-%m-%d %H:%M:%S")
  echo "${t_stamp} $1"
}

function cleanup {
  for i in "${cleanup_items[@]}"; do
      type=$(echo $i|awk -F: '{print $1}')
      item=$(echo $i|awk -F: '{print $2}')
      case $type in
        "snapshot" )
            echo "Deleting DB cluster snapshot: ${item}"
            aws_cmd "/usr/local/bin/aws rds delete-db-cluster-snapshot --db-cluster-snapshot-identifier ${item} --region ${aws_region}"
            if [ $? -ne 0 ]; then
              log_err "Unable to delete DB cluster snapshot: ${item}"
            fi
            result=${aws_cmd_output}
            ;;
        "aurora-instance" )
            echo "Deleting Aurora instance: ${item}"
            aws_cmd "/usr/local/bin/aws rds delete-db-instance --skip-final-snapshot --db-instance-identifier ${item} --region ${aws_region}"
            if [ $? -ne 0 ]; then
              log_err "Unable to delete Aurora instance: ${item}"
            fi
            result=${aws_cmd_output}
            ;;
        "aurora-cluster" )
            echo "Deleting Aurora cluster: ${item}"
            aws_cmd "/usr/local/bin/aws rds delete-db-cluster --skip-final-snapshot --db-cluster-identifier ${item} --region ${aws_region}"
            if [ $? -ne 0 ]; then
              log_err "Unable to delete Aurora cluster: ${item}"
            fi
            result=${aws_cmd_output}
            ;;
        "dumpfile" )
            echo "Removing dump file: ${item}"
            rm -f "${item}"
            if [ $? -ne 0 ]; then
              log_err "Unable to remove dump file ${item}"
            fi
            # Remove temporary dat files
            rm -f /mnt/data/ni_dvacompliance/*.dat
            ;;
      esac
  done
}

: "${READDB_ID:?READDB_ID is not set}"
db_instance_id="${READDB_ID}"
restored_db_cluster_id="${ENVIRONMENT}-olcs-aurora-nidataextract-temp-cluster"
restored_db_instance_id="${ENVIRONMENT}-olcs-aurora-nidataextract-temp-instance"
snapshot_timestamp=$(date +"%Y-%m-%d-%H-%M-%S")
env_id=$(echo "$db_instance_id"|cut -d- -f1)
snapshot_id="$env_id-olcs-aurora-nidataextract-$snapshot_timestamp"
cleanup_items=()

####### Gather source Aurora instance and cluster info

aws_cmd "/usr/local/bin/aws rds wait db-instance-available --region ${aws_region} --db-instance-identifier ${db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "DB Instance not available in the given time: ${db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

aws_cmd "/usr/local/bin/aws rds describe-db-instances --db-instance-identifier ${db_instance_id} --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to describe DB Instances: ${db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi
readonly_db_info=$(echo "${aws_cmd_output}" | /usr/bin/jq -r '.DBInstances[]')
source_cluster_id=$(echo "${readonly_db_info}" | /usr/bin/jq -r '.DBClusterIdentifier')
if [ -z "${source_cluster_id}" ] || [ "${source_cluster_id}" = "null" ]; then
  log_err "DB instance ${db_instance_id} is not attached to an Aurora cluster."
  cleanup
  exit 1
fi

aws_cmd "/usr/local/bin/aws rds wait db-cluster-available --region ${aws_region} --db-cluster-identifier ${source_cluster_id}"
if [ $? -ne 0 ]; then
  log_err "DB Cluster not available in the given time: ${source_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

aws_cmd "/usr/local/bin/aws rds describe-db-clusters --db-cluster-identifier ${source_cluster_id} --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to describe DB Cluster: ${source_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi
readonly_cluster_info=$(echo "${aws_cmd_output}" | /usr/bin/jq -r '.DBClusters[]')

log_msg "Creating cluster snapshot: $snapshot_id"
aws_cmd "/usr/local/bin/aws rds create-db-cluster-snapshot --db-cluster-snapshot-identifier $snapshot_id --db-cluster-identifier $source_cluster_id --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to create DB cluster snapshot: ${source_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

sleep 60
aws_cmd "/usr/local/bin/aws rds wait db-cluster-snapshot-available --db-cluster-snapshot-identifier $snapshot_id --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to verify snapshot availability."
  cleanup
  exit 1
fi
cleanup_items+=("snapshot:$snapshot_id")

subnet_group=$(echo $readonly_db_info | /usr/bin/jq -r '.DBSubnetGroup.DBSubnetGroupName')
if [ $? -ne 0 ]; then
  log_err "Unable to determine subnet group name for new instance."
  cleanup
  exit 1
fi
log_msg "Subnet group: ${subnet_group}."
sec_group=$(echo $readonly_db_info | /usr/bin/jq -r '.VpcSecurityGroups[0].VpcSecurityGroupId')
if [ $? -ne 0 ]; then
  log_err "Unable to determine security group ID for new instance."
  cleanup
  exit 1
fi
log_msg "Security group: ${sec_group}"
param_group=$(echo $readonly_db_info | /usr/bin/jq -r '.DBParameterGroups[0].DBParameterGroupName')
if [ $? -ne 0 ]; then
  log_err "Unable to determine DB parameter group for new instance."
  cleanup
  exit 1
fi
log_msg "DB parameter group: ${param_group}"
db_instance_class=$(echo "${readonly_db_info}" | /usr/bin/jq -r '.DBInstanceClass')
if [ $? -ne 0 ] || [ -z "${db_instance_class}" ] || [ "${db_instance_class}" = "null" ]; then
  log_err "Unable to determine DB instance class for new instance."
  cleanup
  exit 1
fi
log_msg "DB instance class: ${db_instance_class}"
cluster_param_group=$(echo "${readonly_cluster_info}" | /usr/bin/jq -r '.DBClusterParameterGroup')
if [ $? -ne 0 ] || [ -z "${cluster_param_group}" ] || [ "${cluster_param_group}" = "null" ]; then
  log_err "Unable to determine DB cluster parameter group for new cluster."
  cleanup
  exit 1
fi
log_msg "DB cluster parameter group: ${cluster_param_group}"
db_engine=$(echo "${readonly_cluster_info}" | /usr/bin/jq -r '.Engine')
if [ $? -ne 0 ] || [ -z "${db_engine}" ] || [ "${db_engine}" = "null" ]; then
  log_err "Unable to determine engine for source cluster."
  cleanup
  exit 1
fi
log_msg "DB engine: ${db_engine}"

rds_master_pass=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 13 ; echo '')

####### Create new Aurora cluster and instance from snapshot

log_msg "Creating new Aurora cluster: ${restored_db_cluster_id} from snapshot ${snapshot_id}"
aws_cmd "/usr/local/bin/aws rds restore-db-cluster-from-snapshot --db-cluster-identifier ${restored_db_cluster_id} --snapshot-identifier ${snapshot_id} --engine ${db_engine} --region ${aws_region} --db-subnet-group-name ${subnet_group} --vpc-security-group-ids ${sec_group} --db-cluster-parameter-group-name ${cluster_param_group}"
if [ $? -ne 0 ]; then
  log_err "Unable to restore Aurora cluster from snapshot: ${restored_db_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

aws_cmd "/usr/local/bin/aws rds wait db-cluster-available --region ${aws_region} --db-cluster-identifier ${restored_db_cluster_id}"
if [ $? -ne 0 ]; then
  log_err "DB Cluster not available in the given time: ${restored_db_cluster_id}"
  cleanup
  exit 1
fi

log_msg "Creating new Aurora instance: ${restored_db_instance_id} in cluster ${restored_db_cluster_id}"
aws_cmd "/usr/local/bin/aws rds create-db-instance --db-instance-identifier ${restored_db_instance_id} --db-cluster-identifier ${restored_db_cluster_id} --engine ${db_engine} --db-instance-class ${db_instance_class} --region ${aws_region} --db-parameter-group-name ${param_group}"
if [ $? -ne 0 ]; then
  log_err "Unable to create Aurora instance: ${restored_db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

aws_cmd "/usr/local/bin/aws rds wait db-instance-available --region ${aws_region} --db-instance-identifier ${restored_db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "DB Instance not available in the given time: ${restored_db_instance_id}"
  cleanup
  exit 1
fi

sleep 20
aws_cmd "/usr/local/bin/aws rds describe-db-instances --region ${aws_region} --db-instance-identifier ${restored_db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "Unable to describe DB Instance: ${restored_db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi
restore_db=$(echo "${aws_cmd_output}" | /usr/bin/jq -r '.DBInstances[]')
aws_cmd "/usr/local/bin/aws rds describe-db-clusters --region ${aws_region} --db-cluster-identifier ${restored_db_cluster_id}"
if [ $? -ne 0 ]; then
  log_err "Unable to describe DB Cluster: ${restored_db_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi
restore_cluster=$(echo "${aws_cmd_output}" | /usr/bin/jq -r '.DBClusters[]')
db_instance_endpoint=$(echo "${restore_cluster}" | /usr/bin/jq -r '.Endpoint')

log_msg "Aurora cluster available at: $db_instance_endpoint"

if [ "$mode" == "" ]; then
   cleanup_items+=("aurora-instance:${restored_db_instance_id}")
   cleanup_items+=("aurora-cluster:${restored_db_cluster_id}")
fi

####### Apply Aurora configuration to new cluster

log_msg "Modifying database."
aws_cmd "/usr/local/bin/aws rds modify-db-cluster --region ${aws_region} --db-cluster-identifier ${restored_db_cluster_id} --vpc-security-group-ids ${sec_group} --db-cluster-parameter-group-name ${cluster_param_group} --master-user-password ${rds_master_pass} --apply-immediately" "" "" "yes"
if [ $? -ne 0 ]; then
  log_err "Unable to modify database cluster: ${restored_db_cluster_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

sleep 60

aws_cmd "/usr/local/bin/aws rds wait db-cluster-available --region ${aws_region} --db-cluster-identifier ${restored_db_cluster_id}"
if [ $? -ne 0 ]; then
  log_err "DB Cluster not available in the given time after modification: ${restored_db_cluster_id}"
  cleanup
  exit 1
fi
sleep 20

####### Run NI DVACOMPLIANCE JOB

if [ ! -d /mnt/data/scripts/niextract ]; then
  log_err "Unable to find NI extract script directory: /mnt/data/scripts/niextract"
  cleanup
  exit 1
fi
if [ ! -d /mnt/data/scripts/niextract/anonymisation_scripts ]; then
  log_err "Unable to find anonymisation scripts directory: /mnt/data/scripts/niextract/anonymisation_scripts"
  cleanup
  exit 1
fi

log_msg "Running NI_Extract-Anon on: ${db_instance_endpoint}"
cd /mnt/data/scripts/niextract

# Pick DB name (keep old default unless you KNOW the correct one)
: "${READDB_NAME:?READDB_NAME is not set}"
DB_NAME="$READDB_NAME"
log_msg "Using DB_NAME=${DB_NAME}"

# Master username is NOT guaranteed to be READDB_USER.
# The snapshot restore keeps the original master username.
# So read it from the restored instance.
master_user=$(echo "${restore_db}" | /usr/bin/jq -r '.MasterUsername')
CONN_STR="-h${db_instance_endpoint} -u${master_user} -p${rds_master_pass}"
: "${master_user:?Could not read MasterUsername from restored DB instance}"

if [[ "${ENVIRONMENT}" != "PROD" ]]; then
  ./NI_Extract.sh -c "${CONN_STR}" -d "${DB_NAME}" \
    -A -a /mnt/data/scripts/niextract/anonymisation_scripts/anon \
    -f /mnt/data/ni_dvacompliance/temp \
    -X /mnt/data/ni_dvacompliance
else
  ./NI_Extract.sh -c "${CONN_STR}" -d "${DB_NAME}" \
    -X /mnt/data/ni_dvacompliance
fi

if [ $? -ne 0 ]; then
  log_err "NI EXTRACT failed."
  cleanup
  exit 1
fi

output_file="$(find /mnt/data/ni_dvacompliance -type f -name "*.tar.gz" | head -n 1)"
if [ -n "${output_file}" ] && [ -f "${output_file}" ]; then
  log_msg "Found VI Extract output: ${output_file}"
  /usr/local/bin/aws s3 cp "${output_file}" "${S3_DEST}"
  if [ $? -ne 0 ]; then
    log_err "Unable to upload dumpfile to s3 bucket"
    cleanup
    exit 1
  fi
  cleanup_items+=("dumpfile:${output_file}")
fi

####### CLEANUP
cleanup

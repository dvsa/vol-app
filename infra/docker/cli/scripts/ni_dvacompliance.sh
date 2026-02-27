#!/bin/bash
echoerr() { printf '%s\n' "$*" >&2; }

if [ -n "${PROXY:-}" ]; then
  export http_proxy="http://${PROXY}:3128"
  export https_proxy="http://${PROXY}:3128"
fi
export NO_PROXY="${NO_PROXY:-169.254.169.254,169.254.170.2,localhost,127.0.0.1}"

: "${READDB_HOST:?READDB_HOST is not set}"
: "${ENVIRONMENT_NAME:?ENVIRONMENT_NAME is not set}"
ENVIRONMENT="$ENVIRONMENT_NAME"

aws_region="${AWS_REGION:-$(/usr/local/bin/aws configure get region)}"
: "${aws_region:?Could not determine AWS region}"

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

# Debug mode disables the deletion of the temp RDS instance
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
  cmd=$1
  max_retries=$2
  sleep_between=$3
  sensitive=$4

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
            echo "Deleting DB snapshot: ${item}"
            aws_cmd "/usr/local/bin/aws rds delete-db-snapshot --db-snapshot-identifier ${item} --region ${aws_region}"
            if [ $? -ne 0 ]; then
              log_err "Unable to delete DB snapshot: ${item}"
            fi
            result=${aws_cmd_output}
            ;;
        "rds" )
            echo "Deleting RDS instance: ${item}"
            aws_cmd "/usr/local/bin/aws rds delete-db-instance --skip-final-snapshot --db-instance-identifier ${item} --region ${aws_region}"
            if [ $? -ne 0 ]; then
              log_err "Unable to delete RDS instance: ${item}"
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
restored_db_instance_id="${ENVIRONMENT}-olcs-rds-nidataextract-temp"
snapshot_timestamp=$(date +"%Y-%m-%d-%H-%M-%S")
env_id=$(echo "$db_instance_id"|cut -d- -f1)
snapshot_id="$env_id-olcs-rds-nidataextract-$snapshot_timestamp"
cleanup_items=()

####### Create RDS snapsnot from API read replica

# Ensure readdb instance is available before snapshotting

aws_cmd "/usr/local/bin/aws rds wait db-instance-available --region ${aws_region} --db-instance-identifier ${db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "DB Instance not available in the given time: ${db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

log_msg "Creating snapshot: $snapshot_id"
aws_cmd "/usr/local/bin/aws rds create-db-snapshot --db-snapshot-identifier $snapshot_id --db-instance-identifier $db_instance_id --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to create db snapshot: ${db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

sleep 60
aws_cmd "/usr/local/bin/aws rds wait db-snapshot-completed --db-snapshot-identifier $snapshot_id --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to verify snapshot availability."
  cleanup
  exit 1
fi
cleanup_items+=("snapshot:$snapshot_id")

####### Gather subnet/security/param group info from API read replica

aws_cmd "/usr/local/bin/aws rds describe-db-instances --db-instance-identifier ${db_instance_id}  --region ${aws_region}"
if [ $? -ne 0 ]; then
  log_err "Unable to describe DB Instances: ${db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi
readonly_db_info=$(echo "${aws_cmd_output}" | /usr/bin/jq -r .DBInstances[])

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
  log_err "Unable to determine db parameter group for new instance."
  cleanup
  exit 1
fi
log_msg "DB parameter group: ${param_group}"

rds_master_pass=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 13 ; echo '')

####### Create new RDS instance from snapshot

log_msg "Creating new RDS instance: ${restored_db_instance_id} from snapshot ${snapshot_id}"
aws_cmd "/usr/local/bin/aws rds restore-db-instance-from-db-snapshot --db-instance-identifier ${restored_db_instance_id} --db-snapshot-identifier ${snapshot_id} --region ${aws_region} --db-subnet-group-name ${subnet_group} --db-instance-class db.m6g.2xlarge"
if [ $? -ne 0 ]; then
  log_err "Unable to restore RDS instance from snapshot: ${restored_db_instance_id}: ${aws_cmd_output}"
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
db_instance_endpoint=$(echo "${restore_db}" | /usr/bin/jq -r '.Endpoint.Address')

log_msg "RDS instance available at: $db_instance_endpoint"

if [ "$mode" == "" ]; then
   cleanup_items+=("rds:${restored_db_instance_id}")
fi

####### Apply subnet/security/param group info to new instance and reboot

log_msg "Modifying database."
aws_cmd "/usr/local/bin/aws rds modify-db-instance --region ${aws_region} --db-instance-identifier ${restored_db_instance_id} --vpc-security-group-ids ${sec_group} --db-parameter-group-name ${param_group} --master-user-password ${rds_master_pass} --apply-immediately" "" "" "yes"
if [ $? -ne 0 ]; then
  log_err "Unable to modify database: ${restored_db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

sleep 60

aws_cmd "/usr/local/bin/aws rds wait db-instance-available --region ${aws_region} --db-instance-identifier ${restored_db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "DB Instance not available in the given time after subnet modification: ${restored_db_instance_id}"
  cleanup
  exit 1
fi
sleep 20

log_msg "Rebooting RDS instance."
result=$(/usr/local/bin/aws rds reboot-db-instance --db-instance-identifier ${restored_db_instance_id} --region ${aws_region})
if [ $? -ne 0 ]; then
  log_err "Unable to reboot RDS instance: ${restored_db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

sleep 60

aws_cmd "/usr/local/bin/aws rds wait db-instance-available --region ${aws_region} --db-instance-identifier ${restored_db_instance_id}"
if [ $? -ne 0 ]; then
  log_err "DB Instance not available in the given time after subnet modification: ${restored_db_instance_id}"
  cleanup
  exit 1
fi
sleep 20

log_msg "Increasing database volume size."
aws_cmd "/usr/local/bin/aws rds modify-db-instance --region ${aws_region} --db-instance-identifier ${restored_db_instance_id} --iops 1000 --allocated-storage 300 --apply-immediately"
if [ $? -ne 0 ]; then
  log_err "Unable to increase database size: ${restored_db_instance_id}: ${aws_cmd_output}"
  cleanup
  exit 1
fi

####### Run NI DVACOMPLIANCE JOB

if [ ! -d /mnt/data/common/scripts/NI_Extract ]; then
  log_err "Unable to find NI extract script directory: /mnt/data/common/scripts/NI_Extract"
  cleanup
  exit 1
fi
if [ ! -d /mnt/data/common/scripts/anonymisation_scripts ]; then
  log_err "Unable to find anonymisation scripts directory: /mnt/data/common/scripts/anonymisation_scripts"
  cleanup
  exit 1
fi

log_msg "Running NI_Extract-Anon on: ${db_instance_endpoint}"
cd /mnt/data/common/scripts/NI_Extract

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
    -A -a /mnt/data/common/scripts/anonymisation_scripts/anon \
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

output_file=$(find /mnt/data/ni_dvacompliance -type f -name "*.tar.gz")
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

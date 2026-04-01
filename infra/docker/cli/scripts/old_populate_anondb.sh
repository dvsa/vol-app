#!/bin/bash

# This script is responsible for creating an anonymised copy of the production database using the anonymization scripts as part of olcs-etl
# It sends a dump of the anonymized database to a non-prod S3 bucket, as well as creating a snapshot for use in non-production environments
# Anonymised database tables for use in local development environments are also dumped and stored in a non-prod S3 bucket

export http_proxy=http://${PROXY}
export https_proxy=http://${PROXY}
export NO_PROXY=169.254.169.254
nonprod_assume_external_id=${PRODTODEV_ASSUME_ROLE_ID}
readdb=${READDB_ID}
domain=${FULL_DOMAIN}
env=${ENVIRONMENT_NAME}

token=$(curl -s -X PUT "http://169.254.169.254/latest/api/token" -H "X-aws-ec2-metadata-token-ttl-seconds: 21600")
ec2_avail_zone=$(curl -s -H "X-aws-ec2-metadata-token: $token" http://169.254.169.254/latest/meta-data/placement/availability-zone)
ec2_region="`echo \"$ec2_avail_zone\" | sed -e 's:\([0-9][0-9]*\)[a-z]*\$:\\1:'`"

function log_err {
  t_stamp=$(date +"%Y-%m-%d %H:%M:%S")
  echo "${t_stamp} $@" 1>&2;
}

function log_msg {
  t_stamp=$(date +"%Y-%m-%d %H:%M:%S")
  echo "${t_stamp} $1"
}

function wait_rds {
  IFS=':' read -r -a array <<< "$2"
  db_instance_status="unavailable"
  while [[ ! "${array[@]}" =~ "${db_instance_status}" ]]; do
    log_msg "Waiting for RDS instance ($1) to become available."
    sleep 30
    db=$(/usr/local/bin/aws rds describe-db-instances --region $ec2_region --db-instance-identifier $1 | /usr/bin/jq -r '.DBInstances[]')
    db_instance_status=$(echo $db | /usr/bin/jq -r '.DBInstanceStatus')
  done
}

function cleanup {
  unset AWS_ACCESS_KEY_ID
  unset AWS_SECRET_ACCESS_KEY
  unset AWS_SESSION_TOKEN
  for i in "${cleanup_items[@]}"; do
      type=$(echo $i|awk -F: '{print $1}')
      item=$(echo $i|awk -F: '{print $2}')
      case $type in
        "snapshot" )
            echo "Deleting DB snapshot: ${item}"
            result=$(/usr/local/bin/aws rds delete-db-snapshot --db-snapshot-identifier $item --region $ec2_region)
            if [ $? -ne 0 ]; then
              log_err "Unable to delete DB snapshot: ${item}"
            fi
            ;;
        "rds" )
            echo "Deleting RDS instance: ${item}"
            result=$(/usr/local/bin/aws rds delete-db-instance --skip-final-snapshot --db-instance-identifier $item --region $ec2_region)
            if [ $? -ne 0 ]; then
              log_err "Unable to delete RDS instance: ${item}"
            fi
            ;;
        "dumpfile" )
            echo "Removing dump file: ${item}"
            rm -f ${item}
            if [ $? -ne 0 ]; then
              log_err "Unable to remove dump file ${item}"
            fi
            ;;
      esac
  done
}

DATE=$(date +"%Y-%m-%d")
snapshot_timestamp=$(date +"%Y-%m-%d-%H-%M-%S")
db_instance_id=${readdb}
restored_db_instance_id="olcsanondb-rds.$domain-temp"
env_id=$(echo "$db_instance_id"|cut -d- -f1)
olcsreaddb_snapshot_id="$env_id-olcs-rds-olcsreaddb-$snapshot_timestamp"
anondb_snapshot_id="olcs-db-anon-$env-$DATE"
anondb_dump_dir="/mnt/data/anondump"
anondb_tables="template template_test_data translation_key translation_key_text replacement public_holiday fee_type doc_template system_parameter feature_toggle financial_standing_rate"

cleanup_items=()
snapshot_cleanup_items=()

####### Create RDS snapsnot from OLCSDB Read Replica
# Ensure readdb instance is available before snapshotting
wait_rds $db_instance_id 'available'
log_msg "Creating snapshot: ${olcsreaddb_snapshot_id}"
snapshot=$(/usr/local/bin/aws rds create-db-snapshot --db-snapshot-identifier $olcsreaddb_snapshot_id --db-instance-identifier $db_instance_id --region $ec2_region | /usr/bin/jq -r '.DBSnapshot')
if [ $? -ne 0 ]; then
  log_err "Failed to create snapshot."
  exit 1
fi
snapshot_status=$(echo $snapshot | /usr/bin/jq -r '.Status')
snapshot_arn=$(echo $snapshot | /usr/bin/jq -r '.DBSnapshotArn')
if [ "$snapshot_status" != "creating" ]; then
  log_err "Failed to create snapshot."
  exit 1
fi
snapshot_progress=0
while [ $snapshot_progress -ne 100 ]; do
  snapshot_status=$(/usr/local/bin/aws rds describe-db-snapshots --region $ec2_region --db-snapshot-identifier $olcsreaddb_snapshot_id | /usr/bin/jq -r '.DBSnapshots[]')
  snapshot_progress=$(echo $snapshot_status | /usr/bin/jq -r '.PercentProgress')
  log_msg "Progress: ${snapshot_progress}"
  sleep 30
done
# pause for a minute while the snapshot status updates
sleep 60
/usr/local/bin/aws rds wait db-snapshot-completed --db-snapshot-identifier $olcsreaddb_snapshot_id --region $ec2_region
if [ $? -ne 0 ]; then
  log_err "Unable to verify snapshot creation."
  exit 1
fi
cleanup_items+=("snapshot:$olcsreaddb_snapshot_id")

####### Gather subnet/security/param group info from API read replica
readonly_db_info=$(/usr/local/bin/aws rds describe-db-instances --db-instance-identifier $db_instance_id  --region $ec2_region | /usr/bin/jq -r .DBInstances[])
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
  log_err "Unable to determine db parmeter group for new instance."
  cleanup
  exit 1
fi
log_msg "DB parameter group: ${param_group}"

rds_master_pass=$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 13 ; echo '')

####### Create new RDS instance from snapshot
log_msg "Creating new RDS instance: ${restored_db_instance_id} from snapshot ${olcsreaddb_snapshot_id}"
restored_db=$(/usr/local/bin/aws rds restore-db-instance-from-db-snapshot --db-instance-identifier $restored_db_instance_id --db-snapshot-identifier $olcsreaddb_snapshot_id --region $ec2_region --db-subnet-group-name $subnet_group --db-instance-class db.m6g.2xlarge)
if [ $? -ne 0 ]; then
  log_err "Unable to restore RDS instance from snapshot."
  cleanup
  exit 1
fi
restored_db_output=$(echo $restored_db | /usr/bin/jq -r '.DBInstance')
wait_rds $restored_db_instance_id 'available'
db_instance_endpoint=$(/usr/local/bin/aws rds describe-db-instances --region $ec2_region --db-instance-identifier $restored_db_instance_id | /usr/bin/jq -r '.DBInstances[].Endpoint.Address')

log_msg "RDS instance available at: $db_instance_endpoint"

cleanup_items+=("rds:${restored_db_instance_id}")

####### Apply subnet/security/param group info to new instance and reboot
log_msg "Modifying database."
result=$(/usr/local/bin/aws rds modify-db-instance --region $ec2_region --db-instance-identifier $restored_db_instance_id --vpc-security-group-ids $sec_group --db-parameter-group-name $param_group --master-user-password $rds_master_pass --allocated-storage 300 --iops 1000 --apply-immediately)
if [ $? -ne 0 ]; then
  log_err "Unable to modify database."
  cleanup
  exit 1
fi
sleep 60 
wait_rds $restored_db_instance_id 'available:storage-optimization'

log_msg "Rebooting RDS instance."
result=$(/usr/local/bin/aws rds reboot-db-instance --db-instance-identifier $restored_db_instance_id --region $ec2_region)
if [ $? -ne 0 ]; then
  log_err "Unable to reboot RDS instance: ${restored_db_instance_id}"
  cleanup
  exit 1
fi
wait_rds $restored_db_instance_id 'available:storage-optimization'

# Run anonymisation scripts against anon db
if [ -d ${anondb_dump_dir}/temp ]; then
  rm -rf ${anondb_dump_dir}/temp
fi
mkdir ${anondb_dump_dir}/temp
cd /mnt/data/common/scripts/anonymisation_scripts/anon
log_msg "Running anonymisation"
./run_anonymisation.sh -c "-umaster -h${db_instance_endpoint} -p${rds_master_pass}" -d OLCS_RDS_OLCSDB -f ${anondb_dump_dir}/temp -F
if [ $? -ne 0 ]; then
  log_err "Anonymisation process failed."
  cleanup
  exit 1
fi

######## Create RDS snapsnot from newly anonymised DB
# Compile list of and clean up snapshots >30 days old
log_msg "Compiling list of snapshots greater than 30 days old to be deleted"
snapshot_cleanup_items=(`aws rds describe-db-snapshots --region $ec2_region --snapshot-type manual --query "DBSnapshots[?(DBInstanceIdentifier=='$restored_db_instance_id') && (SnapshotCreateTime<='$(date -d "-30 days" "+%Y-%m-%d")')].DBSnapshotIdentifier" --output text`)
if [ $? -ne 0 ]; then
  log_err "Could not retrieve list of snapshots."
  cleanup
  exit 1
fi

if [ ${#snapshot_cleanup_items[@]} -gt 0 ]; then
  for i in ${snapshot_cleanup_items[@]}; do
    snapshot_status="unavailable"
    snapshot_status=$(/usr/local/bin/aws rds describe-db-snapshots --db-snapshot-identifier $i --region $ec2_region | /usr/bin/jq -r '.DBSnapshots[].Status')
    if [ "$snapshot_status" = "available" ]; then
      log_msg "Deleting snapshot: $i"
      /usr/local/bin/aws rds delete-db-snapshot --db-snapshot-identifier $i --region $ec2_region
      if [ $? -ne 0 ]; then
        log_err "Could not delete snapshot $i. Please address this manaually. Continuing..."
      fi
    else
      log_err "Snapshot $i is not in an available state. Please address this manually. Continuing..."
    fi
  done
else
  log_msg "There have been no snapshots identified for deletion"
fi

# Ensure anondb-temp instance is available before snapshotting
wait_rds $restored_db_instance_id 'available'

log_msg "Creating snapshot: $anondb_snapshot_id"
snapshot=$(/usr/local/bin/aws rds create-db-snapshot --db-snapshot-identifier $anondb_snapshot_id --db-instance-identifier $restored_db_instance_id --region $ec2_region | /usr/bin/jq -r '.DBSnapshot')
if [ $? -ne 0 ]; then
  log_err "Failed to create snapshot."
  cleanup
  exit 1
fi
snapshot_status=$(echo $snapshot | /usr/bin/jq -r '.Status')
snapshot_arn=$(echo $snapshot | /usr/bin/jq -r '.DBSnapshotArn')
if [ "$snapshot_status" != "creating" ]; then
  log_err "Failed to create snapshot."
  cleanup
  exit 1
fi
snapshot_progress=0
while [ $snapshot_progress -ne 100 ]; do
  snapshot_status=$(/usr/local/bin/aws rds describe-db-snapshots --region $ec2_region --db-snapshot-identifier $anondb_snapshot_id | /usr/bin/jq -r '.DBSnapshots[]')
  snapshot_progress=$(echo $snapshot_status | /usr/bin/jq -r '.PercentProgress')
  log_msg "Progress: ${snapshot_progress}"
  sleep 30
done
# Pause for a minute while the snapshot status updates
sleep 60
/usr/local/bin/aws rds wait db-snapshot-completed --db-snapshot-identifier $anondb_snapshot_id --region $ec2_region
if [ $? -ne 0 ]; then
  log_err "Unable to verify snapshot creation."
  cleanup
  exit 1
fi
cleanup_items+=("snapshot:${anondb_snapshot_id}")


if [ "${env}" = "APP" ]; then
  # Share newly created snapshot with nonprod
  log_msg "Adding restoreSnapshot permissions for non-production account on ${anondb_snapshot_id}"
  /usr/local/bin/aws rds modify-db-snapshot-attribute --db-snapshot-identifier $anondb_snapshot_id --attribute-name restore --values-to-add "054614622558" --region $ec2_region
  if [ $? -ne 0 ]; then
    log_err "Unable to add restoreSnapshot permissions to snapshot."
    cleanup
    exit 1
  fi
  log_msg "restoreSnapshot permissions successfully added for non-production account."
fi

mysqldump_bin=$(which mysqldump)
log_msg "Dumping ${db_instance_endpoint} database"
$mysqldump_bin -h$db_instance_endpoint -umaster -p$rds_master_pass --routines --triggers --set-gtid-purged=OFF --add-drop-database --databases OLCS_RDS_OLCSDB | gzip > $anondb_dump_dir/olcs-db-anon-$env-$DATE.sql.gz
if [ $? -ne 0 ]; then
  log_err "Unable to dump anonymised database."
  rm -rf $anondb_dump_dir/temp
  cleanup
  exit 1
fi

mysql_bin=$(which mysql)
log_msg "Dumping ${db_instance_endpoint} table names"
$mysql_bin -h$db_instance_endpoint -umaster -p$rds_master_pass OLCS_RDS_OLCSDB -e 'SHOW TABLES;' | sed '/`OLCS_RDS_OLCSDB`/d' > $anondb_dump_dir/olcs-dbtables-anon-$env-$DATE.txt
if [ $? -ne 0 ]; then
  log_err "Unable to dump table names."
  rm -f $anondb_dump_dir/olcs-db*anon-$env-$DATE.*
  rm -rf $anondb_dump_dir/temp
  cleanup
  exit 1
fi

log_msg "Dumping ${db_instance_endpoint} tables"
$mysqldump_bin -h$db_instance_endpoint -umaster -p$rds_master_pass --skip-triggers --skip-routines --set-gtid-purged=OFF --force OLCS_RDS_OLCSDB $anondb_tables | sed 's/`OLCS_RDS_OLCSDB`[.]//g' > $anondb_dump_dir/olcs-db-localdev-anon-$env-$DATE.sql
if [ $? -ne 0 ]; then
  log_err "Unable to dump tables"
  rm -f $anondb_dump_dir/olcs-db*anon-$env-$DATE.*
  rm -rf $anondb_dump_dir/temp
  cleanup
  exit 1
fi

log_msg "Capturing necessary records from ${db_instance_endpoint} document table for extract"
$mysqldump_bin -h$db_instance_endpoint -umaster -p$rds_master_pass --skip-triggers --skip-routines --set-gtid-purged=OFF --force --single-transaction --where="id IN (SELECT document_id FROM doc_template)" OLCS_RDS_OLCSDB document | sed 's/`OLCS_RDS_OLCSDB`[.]//g' >> $anondb_dump_dir/olcs-db-localdev-anon-$env-$DATE.sql
if [ $? -ne 0 ]; then
  log_err "Unable to dump document table"
  rm -f $anondb_dump_dir/olcs-db*anon-$env-$DATE.*
  rm -rf $anondb_dump_dir/temp
  cleanup
  exit 1
fi

source ./s3assume.sh "arn:aws:iam::054614622558:role/DBAM-ProdToDev-AssumeRole" "${nonprod_assume_external_id}"
log_msg "Uploading ${anondb_dump_dir}/olcs-db-anon-$env-${DATE}.sql.gz / \
  ${anondb_dump_dir}/olcs-dbtables-anon-$env-$DATE.txt / \
  ${anondb_dump_dir}/olcs-db-localdev-anon-$env-${DATE}.sql to s3://devapp-olcs-pri-olcs-deploy-s3"
gzip $anondb_dump_dir//olcs-db-localdev-anon-$env-${DATE}.sql
/usr/bin/find $anondb_dump_dir -name "olcs-db*anon-$env-${DATE}.*" -type f -exec /usr/local/bin/aws s3 cp {} s3://devapp-olcs-pri-olcs-deploy-s3/anondata/ \; 1>/dev/null
if [ $? -ne 0 ]; then
  log_err "Unable to upload to s3://devapp-olcs-pri-olcs-deploy-s3"
  rm -f $anondb_dump_dir/olcs-db*anon-$env-$DATE.*
  rm -rf $anondb_dump_dir/temp
  cleanup
  exit 1
fi

cleanup_items+=("dumpfile:${anondb_dump_dir}/olcs-db*anon-$env-${DATE}.*")
####### CLEANUP
# If successul, no longer any need to remove anon db snapshot
cleanup_items=("${cleanup_items[@]/snapshot:${anondb_snapshot_id}}")
rm -rf $anondb_dump_dir/temp
cleanup

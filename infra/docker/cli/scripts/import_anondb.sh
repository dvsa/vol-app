#!/bin/bash

# This script obtains the latest copy of the anonymised production database from an S3 bucket and imports it into the APP anondb instance


export http_proxy=http://${PROXY}:3128
export https_proxy=http://${PROXY}:3128
export NO_PROXY=169.254.169.254
nonprod_assume_external_id=${PRODTODEV_ASSUME_ROLE_ID}
domain=${DOMAIN}

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

log_msg "Downloading latest version of anondb from S3"
source ./s3assume.sh "arn:aws:iam::054614622558:role/DBAM-ProdToDev-AssumeRole" "${nonprod_assume_external_id}"

anondb_dump_dir="/mnt/data/anondump"
anondb_archive_latest=`/usr/local/bin/aws s3 ls s3://devapp-olcs-pri-olcs-deploy-s3/anondata/olcs-db-anon-prod --recursive | sort | tail -n 1 | awk '{print $4}'`
anondb_archive_filename="olcs-db-anon-latest-import.sql.gz"

/usr/local/bin/aws s3 cp s3://devapp-olcs-pri-olcs-deploy-s3/$anondb_archive_latest $anondb_dump_dir/$anondb_archive_filename 1>/dev/null
if [ $? -ne 0 ]; then
 log_err "Unable to download latest anondb dump"
 exit 1
fi

log_msg "Importing ${anondb_archive_filename} into olcsanondb-rds.olcs.${domain}"
zcat $anondb_dump_dir/$anondb_archive_filename | sed 's/`OLCS_RDS_OLCSDB`/`OLCS_RDS_OLCSANONDB`/g' | mysql --defaults-file=/usr/local/conf/anonrds.conf -holcsanondb-rds.${domain} -umaster
if [ $? -ne 0 ]; then
 log_err "Restore of latest anondb dump to anondb failed"
 exit 1
fi

rm -f $anondb_dump_dir/$anondb_archive_filename
log_msg "Import of database complete"
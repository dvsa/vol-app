#!/bin/bash

# The purpose of this script is to take a dump of the read replica database and copy it to s3 for collection by the MI team

echoerr() { printf "%s\n" "$*" >&2; }

DATE=$(date +"%Y%m%d%H%M%S")
READDB_HOST=${READDB_HOST}
BATCH_DB_PASSWORD=${BATCH_DB_PASSWORD}
ENVIRONMENT=${ENVIRONMENT_NAME}

if [ENVIRONMENT == "DEV" || "INT"]; then
    REPORTS_BUCKET="devapp-olcs-pri-integration-reporting-s3"
    INTEGRATION_BUCKET="devapp-mc-pri-integration-data-s3"
    elif [ENVIRONMENT == "PREP" || "PROD"]; then
    REPORTS_BUCKET="app-olcs-pri-integration-reporting-s3"
    INTEGRATION_BUCKET="app-mc-pri-integration-data-s3"
fi
       

mysqldump_bin=$(which mysqldump)
mysql_bin=$(which mysql)

TABLES=$(/usr/bin/mysql -h ${READDB_HOST} -u olcsbatch -p ${BATCH_DB_PASSWORD} --skip-column-names -e "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=database() AND TABLE_TYPE='BASE TABLE' AND TABLE_NAME NOT LIKE '%_hist' AND TABLE_NAME NOT IN ('translation_key_category_link', 'translation_key', 'translation_key_location', 'translation_key_tag_link','DR_EXPECTED_NULLS', 'translation_key_text','replacement','replacement_tag_link','replacement_category_link','dr_table_counts','dr_expected_deletes')" OLCS_RDS_OLCSDB )
/usr/bin/mysqldump -h ${READDB_HOST} -u olcsbatch -p ${BATCH_DB_PASSWORD} --skip-triggers --set-gtid-purged=OFF --no-create-db --no-tablespaces OLCS_RDS_OLCSDB ${TABLES} > /mnt/data/olcsdump/olcsdump-${DATE}.dmp

if [ $? -ne 0 ]; then
    echoerr "ERROR: Database dump failed"
    rm -f /mnt/data/olcsdump/olcsdump-${DATE}.dmp
    exit 1
fi
cd /mnt/data/olcsdump
sha256sum olcsdump-.dmp > /mnt/data/olcsdump//olcsdump-manifest.txt
tar czf /mnt/data/olcsdump/olcsdump-.tar.gz olcsdump-.dmp olcsdump-manifest.txt
if [ $? -ne 0 ]; then
    echoerr "ERROR: Failed to compress dumpfile"
    rm -f /mnt/data/olcsdump/olcsdump-${DATE}.dmp
    exit 1
fi
/usr/local/bin/aws s3 cp /mnt/data/olcsdump/olcsdump-.tar.gz s3://${REPORTS_BUCKET}/olcsdump/olcsdump-.tar.gz 
if [ $? -ne 0 ]; then
    echoerr "ERROR: Upload to S3 bucket failed"
    exit 1
fi

sleep 5

/usr/local/bin/aws s3 cp /mnt/data/olcsdump/olcsdump-.tar.gz s3://${INTEGRATION_BUCKET}/olcsdump/olcsdump-.tar.gz 
if [ $? -ne 0 ]; then
    echoerr "ERROR: Upload to MC Integration S3 bucket for EDH failed"
    exit 1
fi
rm -f /mnt/data/olcsdump/olcsdump-${DATE}.tar.gz /mnt/data/olcsdump/olcsdump-manifest.txt /mnt/data/olcsdump/olcsdump-${DATE}.dmp

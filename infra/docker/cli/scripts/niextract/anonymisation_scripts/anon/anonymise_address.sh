#!/bin/bash

#####################################################################
#                                                                   #
# anonymise address table.                                          #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
ADDRESS_DATA_FILE=$4
ADDRESS_ANON_DATA_FILE=$5
ADDRESS_CSV_FILE=$6

log () {
printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error () {
log "$1"
exit 1
}

get_data() {

conn=$1
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.address ;" | tr '^' ' ' | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$ADDRESS_DATA_FILE
}

anonymise_address () {

# Removed all slow Bash associative array declarations

# Retained mapping tracking reference for documentation:
#addressLineOne -> paon_desc : 5
#addressLineTwo -> saon_desc : 8
#county -> locality : 10
#street -> null ( OLCS-13670 )
#town -> town : 11
#postcode -> poatcode : 12

[ -e $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE

# Replaced slow Bash loop with high-speed awk.

awk -F'^' '
BEGIN {
    srand(); # Seed the random number generator
}
# Pass 1: Read the Comma-Separated Reference File (NR==FNR applies only to the first file parameter passed)
NR==FNR {
    # Dynamically split comma-separated columns into a temporary array
    split($0, csv, ",");
    addr1[FNR]  = csv[2];
    addr2[FNR]  = csv[3];
    twn[FNR]    = csv[4];
    cnt[FNR]    = csv[5];
    pcode[FNR]  = csv[6];
    total_ref = FNR; # Tracks how many total reference records exist
    next;
}
# Pass 2: Stream through the Caret-Separated Database Data File
{
    # Generate independent, unbiased random indices bounded by the total size of your CSV reference data
    r1 = int(rand() * total_ref) + 1;
    r2 = int(rand() * total_ref) + 1;
    r3 = int(rand() * total_ref) + 1;
    r4 = int(rand() * total_ref) + 1;
    r5 = int(rand() * total_ref) + 1;

    # Conditional mask substitution matching your original logic (Field index numbers correlate to your read list)
    # $5=paon_desc, $8=saon_desc, $9=street, $10=locality, $11=town, $12=postcode
    if ($5  != "") $5  = addr1[r1];
    if ($8  != "") $8  = addr2[r2];
    $9  = ""; 
    if ($10 != "") $10 = cnt[r4];
    if ($11 != "") $11 = twn[r3];
    if ($12 != "") $12 = pcode[r5];

    # Directly outputs the modified record using tabs (\t) as the field output separator
    print $0;
}' OFS='\t' "$ADDRESS_CSV_FILE" "$ANON_DATA_DIR/$ADDRESS_DATA_FILE" >> $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE || log_error "anonymise_address FAILED!"

iconv -f utf-8 -t utf-8 -c  $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE >  $ANON_DATA_DIR/tmp-$ADDRESS_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$ADDRESS_ANON_DATA_FILE  $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE

}

reload_address ()
{

connection=$1

mysql --local-infile=1 $connection  --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table address;
SET FOREIGN_KEY_CHECKS = 1;

-- Added index disable command to maximize speed during execution of the data pipeline load.
ALTER TABLE address DISABLE KEYS;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE' INTO table address FIELDS TERMINATED BY '\t'
( id
,@uprn
,@paon_start
,@paon_end
,@paon_desc
,@saon_start
,@saon_end
,@saon_desc
,@street
,@locality
,@town
,@postcode
,@admin_area
,@country_code
,@created_by
,@last_modified_by
,@deleted_date
,@created_on
,@last_modified_on
,@version
,@olbs_key
,@olbs_type
)
SET uprn=nullif(@uprn,'')
,paon_start=nullif(@paon_start,'')
,paon_end=nullif(@paon_end,'')
,paon_desc=nullif(@paon_desc,'')
,saon_start=nullif(@saon_start,'')
,saon_end=nullif(@saon_end,'')
,saon_desc=nullif(@saon_desc,'')
,street=nullif(@street,'')
,locality=nullif(@locality,'')
,town=nullif(@town,'')
,postcode=nullif(@postcode,'')
,admin_area=nullif(@admin_area,'')
,country_code=nullif(@country_code,'')
,created_by=nullif(@created_by,'')
,last_modified_by=nullif(@last_modified_by,'')
,deleted_date=nullif(@deleted_date,'')
,created_on=nullif(@created_on,'')
,last_modified_on=now()
,version=nullif(@version,'')
,olbs_key=nullif(@olbs_key,'')
,olbs_type=nullif(@olbs_type,'');

-- Re-enable indexes cleanly in a single fast block operation after the data finishes writing.
ALTER TABLE address ENABLE KEYS;

SET @DISABLE_TRIGGERS = null;" || log_error "reload_address FAILED!"

}

get_data "$connection"
anonymise_address
reload_address "$connection"
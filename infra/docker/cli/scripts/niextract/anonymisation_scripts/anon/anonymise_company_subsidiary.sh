#!/bin/bash

#####################################################################
#                                                                   #
# anonymise company_subsidiary table.                               #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
SUBSIDIARY_DATA_FILE=$4
SUBSIDIARY_ANON_DATA_FILE=$5
COMPANIES_CSV_FILE=$6

# Removed global Bash associative array declarations

log () {
printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error () {
log "$1"
exit 1
}

get_data() {

conn=$1
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.company_subsidiary ;" | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$SUBSIDIARY_DATA_FILE
}

# Merged read_company_data and anonymise_company_subsidiary 
anonymise_company_subsidiary ()
{

[ -e "$ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE" ] && rm "$ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE"

# Replaced slow Bash loop with high-speed awk.
awk -F'^' '
BEGIN {
    srand(); # Seed the random number generator
}
# Pass 1: Parse the Comma-Separated Reference File up to a max of 10,000 rows
NR==FNR {
    # POSIX-compliant way to cap memory: skip processing if we exceed 10,000 rows
    if (FNR > 10000) {
        next;
    }

    split($0, csv, ",");
    c_name[FNR] = csv[2];
    total_ref = FNR;
    next;
}
# Pass 2: Stream through the Main Caret-Separated Database Data File
{
    # Generate an independent random index bounded by the reference array size
    r1 = int(rand() * total_ref) + 1;

    # Mimics original random company number logic: $((10000000 + RANDOM % 10000000))
    rand_company_no = 10000000 + int(rand() * 10000000);

    # Conditional mask substitution matching your original database column indexes
    # $3=name, $4=company_no
    $4 = rand_company_no;
    if ($3 != "") $3 = c_name[r1];

    # Force $0 to rebuild using OFS even when no fields are modified
    $1 = $1;

    # Stream out the modified line record using tab-separated fields
    print $0;
}' OFS='\t' "$COMPANIES_CSV_FILE" "$ANON_DATA_DIR/$SUBSIDIARY_DATA_FILE" >> $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE || log_error "anonymise_company_subsidiary FAILED!"

iconv -f utf-8 -t utf-8 -c $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$SUBSIDIARY_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$SUBSIDIARY_ANON_DATA_FILE $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE 

}

reload_company_subsidiary ()
{
conn=$1
mysql --local-infile=1 $conn --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table company_subsidiary;
SET FOREIGN_KEY_CHECKS = 1;

-- Added index disable command to maximize speed during execution of the data pipeline load.
ALTER TABLE company_subsidiary DISABLE KEYS;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE' INTO table company_subsidiary FIELDS TERMINATED BY '\t'
(id
,@licence_id
,@name
,@company_no
,@created_by
,@last_modified_by
,@created_on
,@last_modified_on
,@version
,@olbs_key
,@deleted_date)
SET
licence_id=NULLIF(@licence_id,'')
,name=NULLIF(@name,'')
,company_no=NULLIF(@company_no,'')
,created_by=NULLIF(@created_by,'')
,last_modified_by=NULLIF(@last_modified_by,'')
,created_on=NULLIF(@created_on,'')
,last_modified_on=now()
,version=NULLIF(@version,'')
,olbs_key=NULLIF(@olbs_key,'')
,deleted_date=NULLIF(@deleted_date,'');

-- Re-enable indexes cleanly in a single fast block operation after the data finishes writing.
ALTER TABLE company_subsidiary ENABLE KEYS;

SET @DISABLE_TRIGGERS = null;" || log_error "reload_company_subsidiary FAILED!"
}

get_data "$connection"
# Removed read_company_data function call entirely since it is now embedded inside the main block
anonymise_company_subsidiary
reload_company_subsidiary "$connection"
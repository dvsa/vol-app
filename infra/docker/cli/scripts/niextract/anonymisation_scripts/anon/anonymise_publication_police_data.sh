#!/bin/bash

#####################################################################
#                                                                   #
# anonymise publication_police_data table                           #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
POLICE_DATA_FILE=$4
POLICE_ANON_DATA_FILE=$5
NAMES_CSV_FILE=$6

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
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.publication_police_data;"  | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$POLICE_DATA_FILE
}

anonymise_publication_police_data () {

[ -e $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE

OLDIFS=$IFS

# Replaced slow Bash loop with high-speed awk.
awk -F'^' '
BEGIN {
    srand(); # Seed the random number generator
    
    # Pre-populate short month names array for the legacy OLBS date format string
    split("Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec", months, " ");
}
# Pass 1: Parse the Comma-Separated Names Reference File
NR==FNR {
    split($0, csv, ",");
    fore[FNR] = csv[1];
    fam[FNR]  = csv[2];
    total_ref = FNR;
    next;
}
# Pass 2: Stream through the Main Caret-Separated Database Data File
{
    # Generate random indices for masking substitutions
    r1 = int(rand() * total_ref) + 1;
    r2 = int(rand() * total_ref) + 1;

    # Conditional mask substitution matching your original database column indexes
    # $4=forename, $5=family_name, $6=birth_date, $7=olbs_dob
    if ($4 != "") $4 = fore[r1];
    if ($5 != "") $5 = fam[r2];

    # Mimics original random birth date format calculation natively in awk: YYYY-MM-DD
    if ($6 != "") {
        r_year  = 1970 + int(rand() * 30);
        r_month = 1 + int(rand() * 12);
        r_day   = 1 + int(rand() * 28);
        $6 = sprintf("%04d-%02d-%02d", r_year, r_month, r_day);
    }

    # Mimics custom legacy date format calculation natively in awk: "Mmm DD YYYY 12:00AM"
    if ($7 != "") {
        r_year  = 1970 + int(rand() * 30);
        r_month = 1 + int(rand() * 12);
        r_day   = 1 + int(rand() * 28);
        $7 = sprintf("%s %02d %04d 12:00AM", months[r_month], r_day, r_year);
    }

    # Stream out the modified line record using tab-separated fields
    print $0;
}' OFS='\t' "$NAMES_CSV_FILE" "$ANON_DATA_DIR/$POLICE_DATA_FILE" >> $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE || log_error "anonymise_publication_police_data FAILED!"

iconv -f utf-8 -t utf-8 -c $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$POLICE_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$POLICE_ANON_DATA_FILE $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE

IFS=$OLDIFS

}

reload_publication_police_data () {

connection=$1

mysql --local-infile=1 $connection  --show-warnings -vve "use $ANON_DB;
SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;

truncate table publication_police_data;

-- [OPTIMIZATION] Added index disable command to maximize speed during execution of the data pipeline load.
ALTER TABLE publication_police_data DISABLE KEYS;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$POLICE_ANON_DATA_FILE' INTO table publication_police_data FIELDS TERMINATED BY '\t'
(id
,@publication_link_id
,@person_id
,@forename
,@family_name
,@birth_date
,@olbs_dob
,@created_by
,@last_modified_by
,@created_on
,@last_modified_on
,@version,@olbs_key)
 SET publication_link_id=nullif(@publication_link_id,'')
,person_id=nullif(@person_id,'')
,forename=nullif(@forename,'')
,family_name=nullif(@family_name,'')
,birth_date=nullif(@birth_date,'')
,olbs_dob=nullif(@olbs_dob,'')
,created_by=nullif(@created_by,'')
,last_modified_by=nullif(@last_modified_by,'')
,created_on=now(),last_modified_on=now()
,version=nullif(@version,'')
,olbs_key=nullif(@olbs_key,'');

#hack - patch person_id 0

UPDATE publication_police_data
SET person_id = ( SELECT id FROM person WHERE forename='ETL' AND family_name='ETL')
WHERE person_id=0;

-- [OPTIMIZATION] Re-enable indexes cleanly in a single fast block operation after the data finishes writing.
ALTER TABLE publication_police_data ENABLE KEYS;

SET @DISABLE_TRIGGERS = null;
SET FOREIGN_KEY_CHECKS = 1;" || log_error "reload_publication_police_data FAILED!"

}

get_data "$connection"
anonymise_publication_police_data
reload_publication_police_data "$connection"
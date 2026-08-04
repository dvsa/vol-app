#!/bin/bash

#####################################################################
#                                                                   #
# anonymise person table                                            #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
PERSON_DATA_FILE=$4
PERSON_ANON_DATA_FILE=$5
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
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.person;" | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$PERSON_DATA_FILE
}

anonymise_person () {

[ -e $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE

OLDIFS=$IFS

# Replaced slow Bash loop with high-speed awk.
awk -F'^' '
BEGIN {
    srand(); # Seed the random number generator
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
    r3 = int(rand() * total_ref) + 1;

    # Conditional mask substitution matching your original database column indexes
    # $2=forename, $3=family_name, $4=birth_date, $6=other_name
    if ($2 != "") $2 = fore[r1];
    if ($3 != "") $3 = fam[r2];
    if ($6 != "") $6 = fore[r3];

    # Mimics original random birth date format calculation natively in awk: YYYY-MM-DD
    # Random Year (1970-1999), Month (01-12), and Day (01-28)
    if ($4 != "") {
        r_year  = 1970 + int(rand() * 30);
        r_month = 1 + int(rand() * 12);
        r_day   = 1 + int(rand() * 28);
        $4 = sprintf("%04d-%02d-%02d", r_year, r_month, r_day);
    }

    # Stream out the modified line record using tab-separated fields
    $1 = $1; # force $0 rebuild using OFS even when no fields are modified
    print $0;
}' OFS='\t' "$NAMES_CSV_FILE" "$ANON_DATA_DIR/$PERSON_DATA_FILE" >> $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE || log_error "anonymise_person FAILED!"

iconv -f utf-8 -t utf-8 -c $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$PERSON_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$PERSON_ANON_DATA_FILE $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE

IFS=$OLDIFS

}

reload_person () {
connection=$1

mysql --local-infile=1 $connection  --show-warnings -vve "use $ANON_DB;
SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;

truncate table person;

-- Added index disable command to maximize speed during execution of the data pipeline load.
ALTER TABLE person DISABLE KEYS;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$PERSON_ANON_DATA_FILE' INTO table person FIELDS TERMINATED BY '\t' 
(id
,@forename
,@family_name
,@birth_date
,@birth_place
,@other_name
,@title
,@deleted_date
,@created_by
,@last_modified_by
,@created_on
,@last_modified_on
,version
,@olbs_key
,@olbs_type)
SET forename=NULLIF(@forename,'')
,family_name=NULLIF(@family_name,'')
,birth_date=NULLIF(@birth_date,'')
,birth_place=UPPER(NULLIF(@birth_place,''))
,other_name=NULLIF(@other_name,'')
,title=NULLIF(@title,'')
,deleted_date=NULLIF(@deleted_date,'')
,created_by=NULLIF(@created_by,'')
,last_modified_by=NULLIF(@last_modified_by,'')
,created_on=NULLIF(@created_on,'')
,last_modified_on=now()
,olbs_key=NULLIF(@olbs_key,'')
,olbs_type=NULLIF(@olbs_type,'');

# hack - add the skipped row - publication_police_data will be patched to use this FK
insert person (forename,family_name) SELECT 'ETL','ETL';

-- Re-enable indexes cleanly in a single fast block operation after the data finishes writing.
ALTER TABLE person ENABLE KEYS;

SET FOREIGN_KEY_CHECKS = 1;
SET @DISABLE_TRIGGERS = null;" || log_error "reload_person FAILED!"

}

get_data "$connection"
anonymise_person
reload_person "$connection"
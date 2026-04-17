#! /bin/env bash

#####################################################################
#                                                                   #
# anonymise person table					    #
#								    #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
PERSON_DATA_FILE=$4
PERSON_ANON_DATA_FILE=$5
NAMES_CSV_FILE=$6

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

OLDIFS=$IFS

IFS=$'\,'
foreNames=( $(awk -F"," '{ printf "%s,", $1 }' $NAMES_CSV_FILE) )
familyNames=( $(awk -F"," '{ printf "%s,", $2 }' $NAMES_CSV_FILE) )

[ -e $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE

while IFS=$'^' read id forename family_name birth_date birth_place other_name title deleted_date created_by last_modified_by created_on last_modified_on version olbs_key olbs_type ; do

foreName=
familyName=
otherName=
birthDate=

if [[ ! -z $forename ]]; then
    foreName=${foreNames[$((RANDOM%${#foreNames[@]}))]}
fi

if [[ ! -z $family_name ]]; then
    familyName=${familyNames[$((RANDOM%${#familyNames[@]}))]}
fi

if [[ ! -z $other_name ]]; then
    otherName=${foreNames[$((RANDOM%${#foreNames[@]}))]}
fi

if [[ ! -z $birth_date ]]; then
    birthDate=$(date -d "$((RANDOM%30+1970))-$((RANDOM%12+1))-$((RANDOM%28+1)) $((RANDOM%23+1)):$((RANDOM%59+1)):$((RANDOM%59+1))" '+%Y-%m-%d')
fi

printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$foreName" "$familyName" "$birthDate" "$birth_place" "$otherName" "$title" "$deleted_date" "$created_by" "$last_modified_by" "$created_on" "$last_modified_on" "$version" "$olbs_key" "$olbs_type"

done < $ANON_DATA_DIR/$PERSON_DATA_FILE >> $ANON_DATA_DIR/$PERSON_ANON_DATA_FILE || log_error "anonymise_person FAILED!"

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

SET FOREIGN_KEY_CHECKS = 1;
SET @DISABLE_TRIGGERS = null;" || log_error "reload_person FAILED!"

}


get_data "$connection"
anonymise_person
reload_person "$connection"

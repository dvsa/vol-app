#! /bin/env bash

#####################################################################
#                                                                   #
# anonymise publication_police_data table			    #
#								    #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
POLICE_DATA_FILE=$4
POLICE_ANON_DATA_FILE=$5
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
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.publication_police_data;"  | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$POLICE_DATA_FILE
}

anonymise_publication_police_data () {

foreNames=( $(awk -F"," '{ print $1 }' $NAMES_CSV_FILE) )
familyNames=( $(awk -F"," '{ print $2 }' $NAMES_CSV_FILE) )

[ -e $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE

OLDIFS=$IFS

while IFS=$'^' read id publication_link_id person_id forename family_name birth_date olbs_dob created_by last_modified_by created_on last_modified_on version olbs_key; do

foreName=
familyName=
birthDate=
olbsDOB=

if [[ ! -z $forename ]]; then
    foreName=${foreNames[$((RANDOM%${#foreNames[@]}))]}
fi

if [[ ! -z $family_name ]]; then
    familyName=${familyNames[$((RANDOM%${#familyNames[@]}))]}
fi

if [[ ! -z $birth_date ]]; then
    birthDate=$( date -d "$((RANDOM%30+1970))-$((RANDOM%12+1))-$((RANDOM%28+1))" '+%Y-%m-%d')
fi

if [[ ! -z $olbs_dob ]]; then
    olbsDOB=$( date -d "$((RANDOM%30+1970))-$((RANDOM%12+1))-$((RANDOM%28+1)) $((RANDOM%23+1)):$((RANDOM%59+1)):$((RANDOM%59+1))" '+%b %d %Y 12:00AM'
)
fi

printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$publication_link_id" "$person_id" "$foreName" "$familyName" "$birthDate" "$olbsDOB" "$created_by" "$last_modified_by" "$created_on" "$last_modified_on" "$version" "$olbs_key"

done < $ANON_DATA_DIR/$POLICE_DATA_FILE >> $ANON_DATA_DIR/$POLICE_ANON_DATA_FILE || log_error "anonymise_publication_police_data FAILED!"

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

SET @DISABLE_TRIGGERS = null;
SET FOREIGN_KEY_CHECKS = 1;" || log_error "reload_publication_police_data FAILED!"

}

get_data "$connection"
anonymise_publication_police_data
reload_publication_police_data "$connection"

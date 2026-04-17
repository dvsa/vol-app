#! /bin/env bash

#####################################################################
#                                                                   #
# anonymise company_subsidiary table.		                    #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
SUBSIDIARY_DATA_FILE=$4
SUBSIDIARY_ANON_DATA_FILE=$5
COMPANIES_CSV_FILE=$6

declare -A name


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

read_company_data ()
{

declare -i count=1
declare -i max_rows=10000

while read line; do

    name[$count]=$(echo $line | awk -F',' '{ print $2 }')

    (( count++ ))

    if [ $count -gt $max_rows ]; then
        break;
    fi

done < $COMPANIES_CSV_FILE
}

anonymise_company_subsidiary ()
{

[ -e "$ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE" ] && rm "$ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE"

OLDIFS=$IFS

while IFS=$'^' read id licence_id name company_no created_by last_modified_by created_on last_modified_on version olbs_key deleted_date; do

    random_name=
    random_company_no=$((10000000 + RANDOM % 10000000))

    if [[ ! -z $name ]]; then
        random_name="${name[$((RANDOM%${#name[@]}))]}"
    fi

    printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$licence_id" "$random_name" "$random_company_no" "$created_by" "$last_modified_by" "$created_on" "$last_modified_on" "$version" "$olbs_key" "$deleted_date"

done < $ANON_DATA_DIR/$SUBSIDIARY_DATA_FILE >> $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE || log_error "anonymise_company_subsidiary FAILED!"

iconv -f utf-8 -t utf-8 -c $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$SUBSIDIARY_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$SUBSIDIARY_ANON_DATA_FILE $ANON_DATA_DIR/$SUBSIDIARY_ANON_DATA_FILE 

IFS=$OLDIFS

}

reload_company_subsidiary ()
{
conn=$1
mysql --local-infile=1 $conn --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table company_subsidiary;
SET FOREIGN_KEY_CHECKS = 1;

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

SET @DISABLE_TRIGGERS = null;" || log_error "reload_company_subsidiary FAILED!"
}

get_data "$connection"
read_company_data
anonymise_company_subsidiary
reload_company_subsidiary "$connection"

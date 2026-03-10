#! /bin/env bash

#####################################################################
#                                                                   #
# anonymise address table.					    #
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

declare -A addressLineOne
declare -A addressLineTwo
declare -A town
declare -A county
declare -A postcode
declare -i count
count=1

while read line; do
    addressLineOne[$count]=$(echo $line | awk -F',' '{ print $2}')
    addressLineTwo[$count]=$(echo $line | awk -F',' '{ print $3}')
    town[$count]=$(echo $line | awk -F',' '{ print $4}')
    county[$count]=$(echo $line | awk -F',' '{ print $5}')
    postcode[$count]=$(echo $line | awk -F',' '{ print $6}')
    (( count++ ))
done < $ADDRESS_CSV_FILE

#addressLineOne -> paon_desc : 5
#addressLineTwo -> saon_desc : 8
#county -> locality : 10
#street -> null ( OLCS-13670 )
#town -> town : 11
#postcode -> poatcode : 12
Street=

[ -e $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE ] && rm $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE

OLDIFS=$IFS

while IFS=$'^' read id uprn paon_start paon_end paon_desc saon_start saon_end saon_desc street locality town postcode admin_area country_code created_by last_modified_by deleted_date created_on last_modified_on version olbs_key olbs_type ; do

Paon_desc=
Saon_desc=
Locality=
Town=
Postcode=

if [[ ! -z $paon_desc ]]; then
    Paon_desc="${addressLineOne[$((RANDOM%${#addressLineOne[@]}))]}"
fi

if [[ ! -z $saon_desc ]]; then
    Saon_desc="${addressLineTwo[$((RANDOM%${#addressLineTwo[@]}))]}"
fi

if [[ ! -z $town ]]; then
    Town="${town[$((RANDOM%${#town[@]}))]}"
fi

if [[ ! -z $locality ]]; then
    Locality="${county[$((RANDOM%${#county[@]}))]}"
fi

if [[ ! -z $postcode ]]; then
    Postcode="${postcode[$((RANDOM%${#postcode[@]}))]}"
fi

printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$uprn" "$paon_start" "$paon_end" "$Paon_desc" "$saon_start" "$saon_end" "$Saon_desc" "$Street" "$Locality" "$Town" "$Postcode" "$admin_area" "$country_code" "$created_by" "$last_modified_by" "$deleted_date" "$created_on" "$last_modified_on" "$version" "$olbs_key" "$olbs_type"

done < $ANON_DATA_DIR/$ADDRESS_DATA_FILE >> $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE || log_error "anonymise_address FAILED!"

iconv -f utf-8 -t utf-8 -c  $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE >  $ANON_DATA_DIR/tmp-$ADDRESS_ANON_DATA_FILE
mv $ANON_DATA_DIR/tmp-$ADDRESS_ANON_DATA_FILE  $ANON_DATA_DIR/$ADDRESS_ANON_DATA_FILE

IFS=$OLDIFS
}

reload_address ()
{

connection=$1

mysql --local-infile=1 $connection  --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table address;
SET FOREIGN_KEY_CHECKS = 1;

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

SET @DISABLE_TRIGGERS = null;" || log_error "reload_address FAILED!"

}

get_data "$connection"
anonymise_address
reload_address "$connection"

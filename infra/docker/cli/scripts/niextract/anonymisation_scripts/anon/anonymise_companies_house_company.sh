#! /bin/env bash
#set -n
#####################################################################
#                                                                   #
# anonymise companies_house_company table.		            #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
COMPANIES_DATA_FILE=$4
COMPANIES_ANON_DATA_FILE=$5
COMPANIES_CSV_FILE=$6

declare -A name
declare -A addressLineOne
declare -A addressLineTwo
declare -A region
declare -A country
declare -A postcode
declare -A _status


log () {
printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error () {
log "$1"
exit 1
}

get_data() {

conn=$1
mysql --local-infile=1 -sNB $conn -e "SELECT * FROM $ANON_DB.companies_house_company ;" | tr '\t' '^' | sed -e 's/NULL//g' > $ANON_DATA_DIR/$COMPANIES_DATA_FILE
}

read_company_data ()
{

declare -i count=1

while read line; do

    name[$count]="$(echo $line | awk -F',' '{ print $2 }')"
    _status[$count]=$(echo $line | awk -F',' ' { print $4 }')
    addressLineOne[$count]=$(echo $line | awk -F',' '{ print $5 }')
    addressLineTwo[$count]=$(echo $line | awk -F',' '{ print $6 }')
    country[$count]=$(echo $line | awk -F',' '{ print $7 }')
    postcode[$count]=$(echo $line | awk -F',' '{ print $8 }')
    region[$count]=$(echo $line | awk -F',' '{ print $10 }')

    (( count++ ))

done < $COMPANIES_CSV_FILE
}

anonymise_companies_house_company ()
{

null_po_box=
null_premises=

[ -e "$ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE" ] && rm "$ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE"

OLDIFS=$IFS

while IFS=$'^' read id company_number company_name company_status address_line_1 address_line_2 country locality po_box postal_code premises region insolvency_processed created_on last_modified_on version ; do

    random_company_name=
    random_addressLineOne=
    random_addressLineTwo=
    random_region=
    random_country=
    random_postcode=
    random_status=
    random_company_number=$((1000000 + RANDOM % 1000000))

    if [[ ! -z $company_name ]]; then
        random_company_name="${name[$((RANDOM%${#name[@]}))]}"
    fi

    if [[ ! -z $address_line_1 ]]; then
        random_addressLineOne="${addressLineOne[$((RANDOM%${#addressLineOne[@]}))]}"
    fi

    if [[ ! -z $address_line_2 ]]; then
        random_addressLineTwo="${addressLineTwo[$((RANDOM%${#addressLineTwo[@]}))]}"
    fi

    if [[ ! -z $region ]]; then
        random_region="${region[$((RANDOM%${#region[@]}))]}"
    fi

    if [[ ! -z $country ]]; then
        random_country="${country[$((RANDOM%${#country[@]}))]}"
    fi

    if [[ ! -z $postal_code ]]; then
        random_postcode="${postcode[$((RANDOM%${#postcode[@]}))]}"
    fi

    if [[ ! -z $company_status ]]; then
        random_status="${_status[$((RANDOM%${#_status[@]}))]}"
    fi

    printf "%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n" "$id" "$random_company_number" "$random_company_name" "$random_status" "$random_addressLineOne" "$random_addressLineTwo" "$random_country" "$locality" "$null_po_box" "$random_postcode" "$null_premises" "$random_region" "$insolvency_processed" "$created_on" "$last_modified_on" "$version"

done < $ANON_DATA_DIR/$COMPANIES_DATA_FILE >> $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE || log_error "anonymise_companies_house_company FAILED!"

iconv -f utf-8 -t utf-8 -c  $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$COMPANIES_ANON_DATA_FILE
mv  $ANON_DATA_DIR/tmp-$COMPANIES_ANON_DATA_FILE  $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE

IFS=$OLDIFS

}

reload_companies_house_company ()
{
conn=$1
mysql --local-infile=1 $conn --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table companies_house_company;
SET FOREIGN_KEY_CHECKS = 1;

LOAD DATA LOCAL INFILE '$ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE' INTO table companies_house_company FIELDS TERMINATED BY '\t'
(id
,@company_number
,@company_name
,@company_status
,@address_line_1
,@address_line_2
,@country
,@locality
,@po_box
,@postal_code
,@premises
,@region
,@insolvency_processed
,@created_on
,@last_modified_on
,@version)
SET
company_number=NULLIF(@company_number,'')
,company_name=NULLIF(@company_name,'')
,company_status=NULLIF(@company_status,'')
,address_line_1=NULLIF(@address_line_1,'')
,address_line_2=NULLIF(@address_line_2,'')
,country=NULLIF(@country,'')
,locality=UPPER(NULLIF(@locality,''))
,po_box=NULLIF(@po_box,'')
,postal_code=NULLIF(@postal_code,'')
,premises=NULLIF(@premises,'')
,region=NULLIF(@region,'')
,insolvency_processed=NULLIF(@insolvency_processed,'')
,created_on=NULLIF(@created_on,'')
,last_modified_on=now()
,version=NULLIF(@version,'');

SET @DISABLE_TRIGGERS = null;"
}

get_data "$connection"
read_company_data
anonymise_companies_house_company
reload_companies_house_company "$connection"

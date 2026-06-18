#!/bin/bash
#set -n
#####################################################################
#                                                                   #
# anonymise companies_house_company table.                          #
#                                                                   #
#####################################################################

connection=$1
ANON_DB=$2
ANON_DATA_DIR=$3
COMPANIES_DATA_FILE=$4
COMPANIES_ANON_DATA_FILE=$5
COMPANIES_CSV_FILE=$6

# Removed global Bash associative array declarations (declare -A)

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

# Merged read_company_data and anonymise_companies_house_company 
anonymise_companies_house_company ()
{

null_po_box=
null_premises=

[ -e "$ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE" ] && rm "$ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE"

# Replaced slow Bash loop with high-speed awk.
awk -F'^' '
BEGIN {
    srand(); # Seed the random number generator
}
# Pass 1: Parse the Comma-Separated Reference File
NR==FNR {
    split($0, csv, ",");
    c_name[FNR] = csv[2];
    stat[FNR]   = csv[4];
    addr1[FNR]  = csv[5];
    addr2[FNR]  = csv[6];
    cntry[FNR]  = csv[7];
    pcode[FNR]  = csv[8];
    reg[FNR]    = csv[10];
    total_ref   = FNR;
    next;
}
# Pass 2: Stream through the Main Caret-Separated Database Data File
{
    # Generate random indices bounded by the reference file size
    r1 = int(rand() * total_ref) + 1;
    r2 = int(rand() * total_ref) + 1;
    r3 = int(rand() * total_ref) + 1;
    r4 = int(rand() * total_ref) + 1;
    r5 = int(rand() * total_ref) + 1;
    r6 = int(rand() * total_ref) + 1;
    r7 = int(rand() * total_ref) + 1;

    # Mimics original random company number logic: $((1000000 + RANDOM % 1000000))
    rand_company_number = 1000000 + int(rand() * 1000000);

    # Conditional mask substitution matching your original database column indexes
    # $2=company_number, $3=company_name, $4=company_status, $5=address_line_1
    # $6=address_line_2, $7=country, $9=po_box, $10=postal_code, $11=premises, $12=region
    $2 = rand_company_number;
    if ($3  != "") $3  = c_name[r1];
    if ($4  != "") $4  = stat[r2];
    if ($5  != "") $5  = addr1[r3];
    if ($6  != "") $6  = addr2[r4];
    if ($7  != "") $7  = cntry[r5];
    $9  = ""; 
    if ($10 != "") $10 = pcode[r6];
    $11 = ""; 
    if ($12 != "") $12 = reg[r7];

    # Stream out the modified line record using tab-separated fields
    print $0;
}' OFS='\t' "$COMPANIES_CSV_FILE" "$ANON_DATA_DIR/$COMPANIES_DATA_FILE" >> $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE || log_error "anonymise_companies_house_company FAILED!"

iconv -f utf-8 -t utf-8 -c  $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE > $ANON_DATA_DIR/tmp-$COMPANIES_ANON_DATA_FILE
mv  $ANON_DATA_DIR/tmp-$COMPANIES_ANON_DATA_FILE  $ANON_DATA_DIR/$COMPANIES_ANON_DATA_FILE

}

reload_companies_house_company ()
{
conn=$1
mysql --local-infile=1 $conn --show-warnings -vve "use $ANON_DB;

SET FOREIGN_KEY_CHECKS = 0;
SET @DISABLE_TRIGGERS = 1;
truncate table companies_house_company;
SET FOREIGN_KEY_CHECKS = 1;

-- Added index disable command to maximize speed during execution of the data pipeline load.
ALTER TABLE companies_house_company DISABLE KEYS;

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

-- Re-enable indexes cleanly in a single fast block operation after the data finishes writing.
ALTER TABLE companies_house_company ENABLE KEYS;

SET @DISABLE_TRIGGERS = null;"
}

get_data "$connection"
# Removed read_company_data function call entirely since it is now embedded inside the main block
anonymise_companies_house_company
reload_companies_house_company "$connection"
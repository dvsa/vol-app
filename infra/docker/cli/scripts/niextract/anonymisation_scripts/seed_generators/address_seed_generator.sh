#!/bin/env bash
#########################################################################
#
# generate seed data for address. 
# 
# use file from http://download.companieshouse.gov.uk/en_output.html
#
# eg ./address_seed_generator.sh BasicCompanyData-2016-09-01-part1_5.csv 1176308
#
#########################################################################

OUTPUT_FILE=address_seed.sql

# pass in the address source file and the id of last address row

ADDRESS_FILE=$1
MAX_ADDRESS_ID=$2

#ADDRESS_FILE="BasicCompanyData-2016-09-01-part1_5.csv"

TOTAL_UPDATES=10000

echo "generating address updates..."

# ignore blank fields and strip out quotes
# dont update columns that are null
# updates start from end of address table

awk -v address_id=$MAX_ADDRESS_ID -v total_updates=$TOTAL_UPDATES 'BEGIN {FS=",";count=1};{if (NR==1) next;if (count>total_updates) exit; gsub("\"",""); if (length($5) > 0 && length($6) > 0 && (length($7) > 0 &&  length($7) <= 30)&& length($8) > 0 && (length($10) > 0 && length($10) <=8 && index($10, " "))) { printf "update address set paon_desc=IF(paon_desc IS NOT NULL,\"%s\",paon_desc),saon_desc=IF(saon_desc IS NOT NULL,\"%s\",saon_desc),locality=IF(locality IS NOT NULL,\"%s\",locality),town=IF(town IS NOT NULL,\"%s\",town),postcode=IF(postcode IS NOT NULL,\"%s\",postcode) where id=%d;\n",$5,$6,$8,$7,$10,address_id; ++count;--address_id;} }' $ADDRESS_FILE  > $OUTPUT_FILE

echo "...done"


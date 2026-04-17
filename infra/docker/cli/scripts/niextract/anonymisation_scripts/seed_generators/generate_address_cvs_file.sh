#!/bin/env bash
#########################################################################
#
# generate address csv file. 
# 
# use file from http://download.companieshouse.gov.uk/en_output.html
#
# eg ./generator_address_csv_file.sh BasicCompanyData-2016-09-01-part1_5.csv
#
#########################################################################

OUTPUT_FILE=address.csv

# pass in the address source file

ADDRESS_FILE=$1

TOTAL_ROWS=50000

echo "generating address csv file..."

# ignore blank fields and strip out quotes

#RegAddress.AddressLine1 - $5
#RegAddress.AddressLine2 - $6
#RegAddress.PostTown - $7
#RegAddress.County -$8
#RegAddress.PostCode -$10

awk -v total_rows=$TOTAL_ROWS 'BEGIN {FS=",";count=1};{if (NR==1) next;if (count>total_rows) exit; gsub("\"",""); if (length($5) > 0 && length($6) > 0 && (length($7) > 0 &&  length($7) <= 30)&& length($8) > 0 && (length($10) > 0 && length($10) <=8 && index($10, " "))  ) { printf "%d,%s,%s,%s,%s,%s\n",count,$5,$6,$7,$8,$10; ++count;} }' $ADDRESS_FILE  > $OUTPUT_FILE

echo "...done"


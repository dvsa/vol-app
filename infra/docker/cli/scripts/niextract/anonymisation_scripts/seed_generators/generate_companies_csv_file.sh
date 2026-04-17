#!/bin/env bash
#########################################################################
#
# generate comapany csv file. 
# 
# use file from http://download.companieshouse.gov.uk/en_output.html
#
# eg ./generator_company_csv_file.sh BasicCompanyData-2016-09-01-part1_5.csv
#
#########################################################################

OUTPUT_FILE=companies.csv

# pass in the company source file

COMPANY_FILE=$1

TOTAL_ROWS=50000

echo "generating $OUTPUT_FILE..."

# ignore blank fields and strip out quotes

# 1 count
# 2 CompanyName - $1
# 3 CompanyNumber - $2
# 4 CompanyStatus - $12
# 5 RegAddress.AddressLine1 - $5
# 6 RegAddress.AddressLine2 - $6
# 7 RegAddress.Country - $9
# 8 RegAddress.PostCode -$10
# 9 RegAddress.PostTown - $7
# 10 RegAddress.County -$8

awk -v total_rows=$TOTAL_ROWS 'BEGIN {FS=",";count=1};{if (NR==1) next;if (count>total_rows) exit; gsub("\"",""); if (length($1) > 0 && length($2) > 0 && length($5) > 0 && length($6) > 0 && (length($7) > 0 && length($7) <= 30) && length($8) > 0 && length($9) > 0 && (length($10) > 0 && length($10) <=8 && index($10, " ")) && (length($12) > 0 && length($12) <= 32)) { printf "%d,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",count,$1,$2,$12,$5,$6,$9,$10,$7,$8; ++count;} }' $COMPANY_FILE  > $OUTPUT_FILE

echo "...done"


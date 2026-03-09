#!/bin/env bash
#########################################################################
#
# generate seed data for company_house_companies. 
# 
# use file from http://download.companieshouse.gov.uk/en_output.html
#
# eg ./company_house_companies_seed_generator.sh BasicCompanyData-2016-09-01-part1_5.csv 1176308
#
#########################################################################

OUTPUT_FILE=company_house_companies_seed.sql

# pass in the company source file and the id of last company_house_companies row

COMPANY_FILE=$1
MAX_ID=$2

TOTAL_UPDATES=10000

echo "generating company_house_companies updates..."

# ignore blank fields and strip out quotes
# dont update columns that are null
# updates start from end of address table

awk -v company_id=$MAX_ID -v total_updates=$TOTAL_UPDATES 'BEGIN {FS=",";count=1};{if (NR==1) next;if (count>total_updates) exit; gsub("\"",""); if (length($1) > 0 && length($2) > 0 && length($5) > 0 && length($6) > 0 && (length($7) > 0 && length($7) <= 30) && length($8) > 0 && length($9) > 0 && (length($10) > 0 && length($10) <=8 && index($10, " ")) && (length($12) > 0 && length($12) <= 32)) { printf "update companies_house_company set company_number=IF(company_number IS NOT NULL,\"%s\",company_number), company_name=IF(company_name IS NOT NULL,\"%s\",company_name),company_status=IF(company_status IS NOT NULL,\"%s\",company_status),address_line_1=IF(address_line_1 IS NOT NULL,\"%s\",address_line_1),address_line_2=IF(address_line_2 IS NOT NULL,\"%s\",address_line_2),country=IF(country IS NOT NULL,\"%s\",country),po_box=NULL,locality=UPPER(locality),postal_code=IF(postal_code IS NOT NULL,\"%s\",postal_code),region=IF(region IS NOT NULL,\"%s\",region) where id=%d;\n",$2,$1,$12,$5,$6,$9,$10,$8,company_id; ++count;--company_id;} }' $COMPANY_FILE  > $OUTPUT_FILE

#2,1,12,5,6,9,10,8
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
echo "...done"
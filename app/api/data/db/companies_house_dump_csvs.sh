#!/bin/sh
mkdir -m 777 -p /tmp/companies_house

TIMESTAMP=`date +"%Y%m%d%H%M%S"`

FILE1=/tmp/companies_house/companies_${TIMESTAMP}.csv
mysql -uroot -ppassword olcs_be -e "SELECT company_number, company_name, company_status, address_line_1, address_line_2, country, locality, po_box, postal_code, premises, region INTO OUTFILE '${FILE1}' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM companies_house_company;"
sed -i '1i "company_number","company_name","company_status","address_line_1","address_line_2","country","locality","po_box","postal_code","premises","region"' $FILE1

FILE2=/tmp/companies_house/officers_${TIMESTAMP}.csv
mysql -uroot -ppassword olcs_be -e "SELECT c.company_number, c.company_name, c.company_status, o.name, o.date_of_birth INTO OUTFILE '${FILE2}' FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\n' FROM companies_house_officer o INNER JOIN companies_house_company c ON o.company_id=c.id;"
sed -i '1i "company_number","company_name","company_status","name","date_of_birth"' $FILE2

printf "Output files:\n ${FILE1}\n ${FILE2}\n\n"
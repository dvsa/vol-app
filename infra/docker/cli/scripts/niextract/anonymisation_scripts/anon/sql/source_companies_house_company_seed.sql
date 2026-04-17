################################################################
#
#   apply companies_house_company seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' companies_house_company seed...') as '';
source data/companies_house_company_seed.sql

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

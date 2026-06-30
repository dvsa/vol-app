################################################################
#
#   apply organisation seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' organisation seed...') as '';
source data/organisation_seed.sql


# enable triggers
SET @DISABLE_TRIGGERS = NULL;

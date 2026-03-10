################################################################
#
#   apply person seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' person seed...') as '';
source data/person_seed.sql

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

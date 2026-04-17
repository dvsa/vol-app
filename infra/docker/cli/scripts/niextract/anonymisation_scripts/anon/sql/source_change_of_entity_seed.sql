################################################################
#
#   apply change_of_entity seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' change_of_entity seed...') as '';
source data/change_of_entity_seed.sql

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

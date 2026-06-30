################################################################
#
#   apply address seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' address seed...') AS '';

source data/address_seed.sql

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

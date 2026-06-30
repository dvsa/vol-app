################################################################
#
#   apply trading_name seed data.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' trading_name seed...') as '';
source data/trading_name_seed.sql

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

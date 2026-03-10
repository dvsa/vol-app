################################################################
#
#   update trading names.
#
################################################################
SET SESSION group_concat_max_len = 1000000;

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' updating trading names...') AS '';

DROP TABLE IF EXISTS trading_name_seed;
CREATE TABLE trading_name_seed(
	id int,
	trading_name varchar(160));

LOAD DATA LOCAL INFILE 'data/tradingnames.csv' INTO TABLE trading_name_seed
FIELDS TERMINATED BY ',';
        
SET @trading_name_list = (SELECT GROUP_CONCAT(trading_name) FROM trading_name_seed group by NULL);
SET @trading_name_seed_count = (SELECT COUNT(*) FROM trading_name_seed);

UPDATE trading_name
SET name = IF(name IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@trading_name_list, ',', CEIL(RAND() * @trading_name_seed_count))), ',', -1),name);

DROP TABLE IF EXISTS trading_name_seed;

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...updating trading names complete.') AS '';


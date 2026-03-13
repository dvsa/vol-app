################################################################
#
# anonymise bus_reg data.
#
################################################################
SET SESSION group_concat_max_len = 1000000;

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' updating bus_reg...') as '';

DROP TEMPORARY TABLE IF EXISTS bus_reg_seed;
CREATE TEMPORARY TABLE bus_reg_seed(
	id int,
	start_point varchar(100),
	finish_point varchar(100));

LOAD DATA LOCAL INFILE 'data/busregs.csv' INTO TABLE bus_reg_seed
FIELDS TERMINATED BY '~'
		ENCLOSED BY '"';

DELETE FROM bus_reg_seed
WHERE id > 1000;

SET @alphabet = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
SET @numbers = '1,2,3,4,5,6,7,8,9,0';

SET @startList = (SELECT GROUP_CONCAT(start_point SEPARATOR '~') FROM bus_reg_seed group by NULL);
SET @finishList = (SELECT GROUP_CONCAT(finish_point SEPARATOR '~') FROM bus_reg_seed group by NULL);
SET @busreg_count = (SELECT COUNT(*) FROM bus_reg_seed);

UPDATE bus_reg	
SET start_point = IF(start_point IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@startList, '~', CEIL(RAND() * @busreg_count))), '~', -1),start_point),
    finish_point = IF(finish_point IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@finishList, '~', CEIL(RAND() * @busreg_count))), '~', -1),finish_point),
    via = IF(via IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='via' ORDER BY RAND() LIMIT 1),via),
    other_details = IF(other_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='other_details' ORDER BY RAND() LIMIT 1),other_details),
    manoeuvre_detail = IF(manoeuvre_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='manoeuvre_detail' ORDER BY RAND() LIMIT 1),manoeuvre_detail),
    new_stop_detail = IF(new_stop_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='new_stop_detail' ORDER BY RAND() LIMIT 1),new_stop_detail),
    not_fixed_stop_detail = IF(not_fixed_stop_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='not_fixed_stop_detail' ORDER BY RAND() LIMIT 1),not_fixed_stop_detail),
    subsidy_detail = IF(subsidy_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='subsidy_detail' ORDER BY RAND() LIMIT 1),subsidy_detail),
    route_description = IF(route_description IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='route_description' ORDER BY RAND() LIMIT 1),route_description),
    stopping_arrangements = IF(stopping_arrangements IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='stopping_arrangements' ORDER BY RAND() LIMIT 1),stopping_arrangements),
    trc_notes = IF(trc_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='trc_notes' ORDER BY RAND() LIMIT 1),trc_notes),
    reason_cancelled = IF(reason_cancelled IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='reason_cancelled' ORDER BY RAND() LIMIT 1),reason_cancelled),
    reason_refused = IF(reason_refused IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='reason_refused' ORDER BY RAND() LIMIT 1),reason_refused),
    reason_sn_refused = IF(reason_sn_refused IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='reason_sn_refused' ORDER BY RAND() LIMIT 1),reason_sn_refused),
    quality_partnership_details = IF(quality_partnership_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='quality_partnership_details' ORDER BY RAND() LIMIT 1),quality_partnership_details),
    quality_contract_details = IF(quality_contract_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_reg' AND column_name ='quality_contract_details' ORDER BY RAND() LIMIT 1),quality_contract_details);

/* busregs seed by ID. */ 
source data/busregs_seed.sql

DROP TABLE IF EXISTS bus_reg_seed;

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...updating bus_reg complete.') as '';


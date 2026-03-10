################################################################
#
# anonymise name data.
#
################################################################
SET SESSION group_concat_max_len = 1000000;

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' updating names...') as '';

DROP TABLE IF EXISTS name_seed;

CREATE TABLE name_seed(
	forename varchar(50), 
	family_name varchar(50));

LOAD DATA LOCAL INFILE 'data/names.csv' INTO TABLE name_seed
FIELDS TERMINATED BY ',';

SET @forenameList = (SELECT GROUP_CONCAT(forename) FROM name_seed group by NULL);
SET @familynameList = (SELECT GROUP_CONCAT(family_name) FROM name_seed group by NULL);
SET @name_seed_count = (SELECT COUNT(*) FROM name_seed);

SELECT CONCAT(now(),' ...name_seed imported...') as '';

UPDATE complaint
SET vrm = IF(vrm IS NOT NULL,CONCAT("VRM", complaint.id),vrm),
    driver_forename = IF(driver_forename IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),driver_forename),
    driver_family_name = IF(driver_family_name IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),driver_family_name),
	description = IF(description IS NOT NULL,"complaint description",description);

SELECT CONCAT(now(),' ...complaint updated...') as '';

UPDATE conviction
SET person_firstname= IF(person_firstname IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),person_firstname),
    person_lastname= IF(person_lastname IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),person_lastname),
    penalty = IF(penalty IS NOT NULL,"SP30",penalty),
	costs = IF(costs IS NOT NULL,"Costs.",costs),
	birth_date = IF(birth_date IS NOT NULL,concat_ws('-', CEIL(RAND() * 59 + 1941), CEIL(RAND() * 12), CEIL(RAND() * 28)),birth_date),
    notes = IF(notes IS NOT NULL,"Conviction notes.",notes),
	taken_into_consideration = IF(taken_into_consideration IS NOT NULL,"Taken into consideration.",taken_into_consideration),
	operator_name = IF(operator_name IS NOT NULL,"Operator Name",operator_name);

SELECT CONCAT(now(),' ...conviction updated...') as '';

UPDATE previous_conviction
SET forename= IF(forename IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),forename),
    family_name= IF(family_name IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),family_name),
	birth_date = IF(birth_date IS NOT NULL,concat_ws('-', CEIL(RAND() * 59 + 1941), CEIL(RAND() * 12), CEIL(RAND() * 28)),birth_date),
	category_text = IF(category_text IS NOT NULL,"Category",category_text),
	notes = IF(notes IS NOT NULL,"Previous conviction notes.",notes),
	court_fpn = IF(court_fpn IS NOT NULL,"Court fpn.",court_fpn),
	penalty = IF(penalty IS NOT NULL,"Penalty",penalty);

SELECT CONCAT(now(),' ...previous_conviction updated...') as '';

UPDATE transport_manager
SET nysiis_forename= IF(nysiis_forename IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),nysiis_forename),
	nysiis_family_name=IF(nysiis_family_name IS NOT NULL,SUBSTRING_INDEX((SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1),nysiis_family_name);

SELECT CONCAT(now(),' ...transport_manager updated...') as '';

UPDATE companies_house_officer
SET name=IF(name IS NOT NULL,CONCAT(UPPER(SUBSTRING_INDEX( (SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1)),", ",
         SUBSTRING_INDEX( (SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count))), ',', -1)),name);

SELECT CONCAT(now(),' ...companies_house_officer updated...') as '';

UPDATE event_history
SET change_made_by=IF(change_made_by IS NOT NULL, CONCAT(SUBSTRING_INDEX(SUBSTRING_INDEX(@forenameList, ',', CEIL(RAND() * @name_seed_count)), ",", -1), ' ', SUBSTRING_INDEX(SUBSTRING_INDEX(@familynameList, ',', CEIL(RAND() * @name_seed_count)), ",", -1)), change_made_by);

SELECT CONCAT(now(),' ...event_history updated...') as '';

DROP TABLE IF EXISTS name_seed;

SELECT CONCAT(now(),' ...name_seed dropped...') as '';

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...updating names complete.') as '';


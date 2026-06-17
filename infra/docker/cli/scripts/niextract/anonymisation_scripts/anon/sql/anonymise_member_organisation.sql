################################################################
#
#   update member organisation.
#
################################################################
SET SESSION group_concat_max_len = 1000000;

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' updating member organisation names...') AS '';

DROP TABLE IF EXISTS organisation_member_seed;
CREATE TABLE organisation_member_seed(
  id int,
  organisation_name varchar(160));

LOAD DATA LOCAL INFILE 'data/tradingnames.csv' INTO TABLE organisation_member_seed
FIELDS TERMINATED BY ',';

SET @organisation_member_list = (SELECT GROUP_CONCAT(organisation_name) FROM organisation_member_seed group by NULL);
SET @organisation_member_seed_count = (SELECT COUNT(*) FROM organisation_member_seed);

ALTER TABLE organisation_member_seed ADD COLUMN seq_id INT AUTO_INCREMENT PRIMARY KEY;

UPDATE event_history e
LEFT JOIN organisation_member_seed o ON (o.seq_id = 1 + (e.id % @organisation_member_seed_count))
SET e.member_of_organisation = IF(e.member_of_organisation IS NOT NULL, o.organisation_name, e.member_of_organisation)
WHERE e.member_of_organisation IS NOT NULL;

DROP TABLE IF EXISTS organisation_member_seed;

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...updating member organisation names complete.') AS '';
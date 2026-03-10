################################################################
#
# anonymise organisation for NI Extract.
#
################################################################

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' anonymising organisation for NI Extract...') AS '';

UPDATE organisation
SET name = CONCAT('ABC ',id)
WHERE NAME IS NOT NULL;

# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' ...anonymising organisation complete.') AS '';


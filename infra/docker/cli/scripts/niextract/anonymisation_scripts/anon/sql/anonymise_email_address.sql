################################################################
#
# Anonymise email address.
#
################################################################
SET SESSION group_concat_max_len = 1000000;
SET @DISABLE_TRIGGERS = 1;

SET @tlds = ".co.uk,.com,.org.uk,.london";

SELECT CONCAT(now(),' start anonymisation of email addresses...') AS '';

# create domain array from org names

DROP TABLE IF EXISTS tempDomains;

CREATE TEMPORARY TABLE tempDomains
AS SELECT NAME,
   LEFT(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(name),' ',''),"'",''),'?',''),'{',''),'}',''),'(',''),')',''),'&',''),'*',''),'.',''),'/',''),'-',''),',',''),':',''),'$',''),'+',''),'`',''),'!',''),'£',''),'[',''),']',''),'_',''),'>',''),'<',''),';',''),'%',''),'=',''),20) AS domain
FROM organisation
ORDER BY RAND()
LIMIT 1000; 
 
SET @domains = (SELECT GROUP_CONCAT(domain SEPARATOR '~') FROM tempDomains group by NULL);

# create email address array

DROP TABLE IF EXISTS tempEmail;

CREATE TEMPORARY TABLE tempEmail
AS SELECT CONCAT(LEFT(REPLACE(REPLACE(LOWER(forename),' ',''),"'",''),20),'.',
LEFT(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(family_name),' ',''),'-',''),"'",''),'&',''),20),'@',SUBSTRING_INDEX((SUBSTRING_INDEX(@domains, '~', CEIL(RAND() * 1000))), '~', -1),SUBSTRING_INDEX((SUBSTRING_INDEX(@tlds, ',', CEIL(RAND() * 4))), ',', -1)) AS email_address
FROM person
WHERE (forename IS NOT NULL AND forename <> '')
AND (family_name IS NOT NULL AND family_name <> '')
ORDER BY RAND()
LIMIT 1000;

SET @email_address = (SELECT GROUP_CONCAT(email_address SEPARATOR '~') FROM tempEmail group by NULL);

UPDATE bus_reg
SET organisation_email = IF(organisation_email IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),organisation_email);

UPDATE contact_details cd
SET email_address = NULL WHERE
cd.person_id is NULL;


UPDATE contact_details cd
JOIN person p ON p.id = cd.person_id
SET email_address=IF(email_address IS NOT NULL,
(
SELECT CONCAT(LEFT(REPLACE(REPLACE(LOWER(forename),' ',''),"'",''),20),'.',
                                   LEFT(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(family_name),' ',''),'-',''),"'",''),'&',''),20),
                                   '@',
                                   SUBSTRING_INDEX(SUBSTRING_INDEX(@domains, '~', CEIL(RAND() * 1000)), '~', -1),
                                   SUBSTRING_INDEX(SUBSTRING_INDEX(@tlds, ',', CEIL(RAND() * 4)), ',', -1))
),email_address)
WHERE (forename IS NOT NULL AND forename <> '')
AND (family_name IS NOT NULL AND family_name <> '');  

UPDATE contact_details cd
JOIN person p ON p.id = cd.person_id
SET email_address=IF(email_address IS NOT NULL,
(
SELECT CONCAT(LEFT(REPLACE(REPLACE(LOWER(forename),' ',''),"'",''),20),
                                   '@',
                                   SUBSTRING_INDEX(SUBSTRING_INDEX(@domains, '~', CEIL(RAND() * 1000)), '~', -1),
                                   SUBSTRING_INDEX(SUBSTRING_INDEX(@tlds, ',', CEIL(RAND() * 4)), ',', -1))
),email_address)
WHERE (forename IS NOT NULL AND forename <> '')
AND family_name IS NULL;  

UPDATE ebsr_submission
SET organisation_email_address = IF(organisation_email_address IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),organisation_email_address);

UPDATE enforcement_area
SET email_address =  IF(email_address IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),email_address);

UPDATE inspection_email
SET sender_email_address =  IF(sender_email_address IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),sender_email_address);

UPDATE local_authority
SET email_address =  IF(email_address IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),email_address);

UPDATE recipient
SET email_address = IF(email_address IS NOT NULL,(SUBSTRING_INDEX((SUBSTRING_INDEX(@email_address, '~', CEIL(RAND() * 1000))), '~', -1)),email_address);

DROP TABLE IF EXISTS tempDomains; 
DROP TABLE IF EXISTS tempEmail; 

SET @DISABLE_TRIGGERS = NULL; 

SELECT CONCAT(now(),' ...anonymisation of email addresses complete.') AS '';
 
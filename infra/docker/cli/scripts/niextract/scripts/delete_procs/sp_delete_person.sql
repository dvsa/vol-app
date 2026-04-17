
DROP PROCEDURE IF EXISTS sp_delete_person;
DELIMITER $$
CREATE PROCEDURE sp_delete_person()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete person started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    DROP TABLE IF EXISTS `tmpPerson`;
    CREATE TEMPORARY TABLE `tmpPerson` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );

    SELECT 'Identifying person rows to delete.' AS '' ;
  
    INSERT INTO tmpPerson (id)
    select id from person;

    DELETE FROM tmpPerson
    WHERE id IN (
        SELECT DISTINCT original_person_id
        FROM application_organisation_person);
    
    DELETE FROM tmpPerson
    WHERE id IN (
        SELECT DISTINCT person_id
        FROM application_organisation_person);

    DELETE FROM tmpPerson 
    WHERE id IN (
	    SELECT DISTINCT person_id 
	    FROM contact_details);

    DELETE FROM tmpPerson
    WHERE id IN (
        SELECT DISTINCT person_id
        FROM organisation_person);

    DELETE FROM tmpPerson
    WHERE id IN (
        SELECT DISTINCT person_id
        FROM publication_police_data);
 
    DELETE FROM tmpPerson
    WHERE id IN (
        SELECT DISTINCT person_id
        FROM disqualification);
        
    DROP TABLE IF EXISTS `tmpPersonBatch`;
    CREATE TEMPORARY TABLE `tmpPersonBatch` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );

    SELECT COUNT(*)
    INTO @total
	FROM tmpPerson;

    SELECT CONCAT(@total,' person rows to delete.') AS '' ;
  
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO

        INSERT tmpPersonBatch
        SELECT id FROM tmpPerson
        LIMIT 10000;
        

    
        DELETE FROM person
        WHERE id IN ( SELECT id from tmpPersonBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpPerson
        WHERE id IN ( SELECT id from tmpPersonBatch);
        

    
        SELECT CONCAT(@total,' person rows deleted.') AS '';

        truncate table tmpPersonBatch;
        
    END WHILE;

    DROP TABLE IF EXISTS `tmpPerson`;
    DROP TABLE IF EXISTS `tmpPersonBatch`;

    SELECT CONCAT('delete person finished at ',now()) AS '' ; 
    
END
$$


  
  

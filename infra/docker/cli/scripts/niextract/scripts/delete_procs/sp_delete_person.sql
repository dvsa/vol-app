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

    SELECT 'Identifying person rows to delete.' AS '' ;
    
    DROP TABLE IF EXISTS `tmpPerson`;
    CREATE TEMPORARY TABLE `tmpPerson` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );
  
    INSERT INTO tmpPerson (id)
    SELECT p.id 
    FROM person p
    LEFT JOIN application_organisation_person aop1 ON p.id = aop1.original_person_id
    LEFT JOIN application_organisation_person aop2 ON p.id = aop2.person_id
    LEFT JOIN contact_details cd                   ON p.id = cd.person_id
    LEFT JOIN organisation_person op               ON p.id = op.person_id
    LEFT JOIN publication_police_data ppd          ON p.id = ppd.person_id
    LEFT JOIN disqualification d                   ON p.id = d.person_id
    WHERE aop1.original_person_id IS NULL
      AND aop2.person_id IS NULL
      AND cd.person_id IS NULL
      AND op.person_id IS NULL
      AND ppd.person_id IS NULL
      AND d.person_id IS NULL;

    DROP TABLE IF EXISTS `tmpPersonBatch`;
    CREATE TEMPORARY TABLE `tmpPersonBatch` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );

    SELECT COUNT(*)
    INTO @total
    FROM tmpPerson;

    SELECT CONCAT(@total,' person rows to delete.') AS '' ;
  
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        INSERT INTO tmpPersonBatch (id)
        SELECT id FROM tmpPerson
        LIMIT 10000;
        
        DELETE FROM person
        WHERE id IN (SELECT id FROM tmpPersonBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpPerson
        WHERE id IN (SELECT id FROM tmpPersonBatch);
        
        SELECT CONCAT(@total,' person rows deleted.') AS '';

        TRUNCATE TABLE tmpPersonBatch;
        
        COMMIT;
        START TRANSACTION;
        
    END WHILE;

    COMMIT;

    DROP TABLE IF EXISTS `tmpPerson`;
    DROP TABLE IF EXISTS `tmpPersonBatch`;

    SELECT CONCAT('delete person finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
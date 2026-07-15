DROP PROCEDURE IF EXISTS sp_delete_disqualification_2;
DELIMITER $$
CREATE PROCEDURE sp_delete_disqualification_2()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete disqualification started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM disqualification d
    LEFT JOIN person p ON d.person_id = p.id
    LEFT JOIN organisation o ON d.organisation_id = o.id
    WHERE (d.person_id IS NOT NULL AND p.id IS NULL)
       OR (d.organisation_id IS NOT NULL AND o.id IS NULL);

    SELECT CONCAT(@total,' disqualification rows to delete.') AS '' ;

    SET @total_deleted := 0;
    SET @rowcount := 10000;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
    
        DELETE d FROM disqualification d
        LEFT JOIN person p ON d.person_id = p.id
        LEFT JOIN organisation o ON d.organisation_id = o.id
        WHERE (d.person_id IS NOT NULL AND p.id IS NULL)
           OR (d.organisation_id IS NOT NULL AND o.id IS NULL)
        LIMIT 10000;
  
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
    
        SELECT CONCAT(@total_deleted,' disqualification rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;

    COMMIT;

    SELECT CONCAT('delete disqualification finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
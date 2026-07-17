DROP PROCEDURE IF EXISTS sp_delete_organisation;
DELIMITER $$
CREATE PROCEDURE sp_delete_organisation()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete organisation started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SELECT COUNT(*)
    INTO @total
    FROM organisation o
    LEFT JOIN licence l ON o.id = l.organisation_id
    WHERE l.organisation_id IS NULL;

    SELECT CONCAT(@total,' organisation rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        DELETE FROM organisation
        WHERE id IN (
            SELECT id FROM (
                SELECT o.id
                FROM organisation o
                LEFT JOIN licence l ON o.id = l.organisation_id
                WHERE l.organisation_id IS NULL
                LIMIT 10000
            ) AS batch
        );

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    


        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
    
    SELECT CONCAT(@total,' organisation rows deleted.') AS '';
  
    SELECT CONCAT('delete organisation finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
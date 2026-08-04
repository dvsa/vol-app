DROP PROCEDURE IF EXISTS sp_delete_bus_reg;
DELIMITER $$
CREATE PROCEDURE sp_delete_bus_reg()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete bus_reg started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM bus_reg br
    LEFT JOIN licence l ON br.licence_id = l.id
    WHERE l.id IS NULL;

    SELECT CONCAT(@total,' bus_reg rows to delete.') AS '' ;
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        DELETE FROM bus_reg
        WHERE id IN (
            SELECT id FROM (
                SELECT br.id
                FROM bus_reg br
                LEFT JOIN licence l ON br.licence_id = l.id
                WHERE l.id IS NULL
                LIMIT 10000
            ) AS batch
        );

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' bus_reg rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
    
    SELECT CONCAT('delete bus_reg finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
DROP PROCEDURE IF EXISTS sp_delete_scan;
DELIMITER $$
CREATE PROCEDURE sp_delete_scan()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete scan started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM scan;

    SELECT CONCAT(@total,' scan rows to delete.') AS '' ;

    SET @total_deleted := 0;
    SET @rowcount := 10000;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
    
        DELETE FROM scan
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
    
        SELECT CONCAT(@total_deleted,' scan rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
    
    SELECT CONCAT('delete scan finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
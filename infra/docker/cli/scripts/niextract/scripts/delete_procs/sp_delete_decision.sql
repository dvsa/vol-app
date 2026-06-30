DROP PROCEDURE IF EXISTS sp_delete_decision;
DELIMITER $$
CREATE PROCEDURE sp_delete_decision()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete decision started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM decision
    WHERE is_ni = 0;

    SELECT CONCAT(@total,' decision rows to delete.') AS '' ;

    SET @total_deleted := 0;
    SET @rowcount := 10000;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
    
        DELETE FROM decision
        WHERE is_ni = 0
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
    
        SELECT CONCAT(@total_deleted,' decision rows deleted.') AS '';  

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
    
    set autocommit=1;

    SELECT CONCAT('delete decision finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
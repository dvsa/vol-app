
DROP PROCEDURE IF EXISTS sp_delete_trading_name;
DELIMITER $$
CREATE PROCEDURE sp_delete_trading_name()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete trading_name started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM trading_name
    WHERE licence_id IS NULL AND organisation_id IS NULL;

    SELECT CONCAT(@total,' trading_name rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO



        DELETE FROM trading_name
        WHERE licence_id IS NULL
        AND organisation_id IS NULL
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' trading_name rows deleted.') AS '';

    END WHILE;
    
    SELECT CONCAT(@rowcount,' trading_name rows deleted.') AS '';
  
    set autocommit=1;

    SELECT CONCAT('delete trading_name finished at ',now()) AS '' ; 
    
END
$$


  
  

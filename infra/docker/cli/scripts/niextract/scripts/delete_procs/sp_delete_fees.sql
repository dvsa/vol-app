
DROP PROCEDURE IF EXISTS sp_delete_fees;
DELIMITER $$
CREATE PROCEDURE sp_delete_fees()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete fees started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM fee
    WHERE application_id IS NULL
    AND bus_reg_id IS NULL
    AND licence_id IS NULL;

    SELECT CONCAT(@total,' fee rows to delete.') AS '' ;
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO



        DELETE FROM fee
        WHERE application_id IS NULL
        AND bus_reg_id IS NULL
        AND licence_id IS NULL
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' fee rows deleted') AS '';

    END WHILE;
    
    set autocommit=1;

    SELECT CONCAT('delete fees finished at ',now()) AS '' ; 
    
END
$$


  
  

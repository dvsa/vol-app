
DROP PROCEDURE IF EXISTS sp_delete_reason;
DELIMITER $$
CREATE PROCEDURE sp_delete_reason()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete reason started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM reason
    WHERE is_ni = 0;

    SELECT CONCAT(@total,' reason rows to delete.') AS '' ;


    
    DELETE FROM reason
    WHERE is_ni = 0;

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' reason rows deleted.') AS '';
    
    SELECT CONCAT('delete reason finished at ',now()) AS '' ; 
    
END
$$


  
  

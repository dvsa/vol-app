
DROP PROCEDURE IF EXISTS sp_delete_enforcement_area;
DELIMITER $$
CREATE PROCEDURE sp_delete_enforcement_area()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete enforcement_area started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM enforcement_area
    WHERE id <> 'EA-N';

    SELECT CONCAT(@total,' enforcement_area rows to delete.') AS '' ;


    
    DELETE FROM enforcement_area
    WHERE id <> 'EA-N';

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' enforcement_area rows deleted.') AS '';
    
    SELECT CONCAT('delete enforcement_area finished at ',now()) AS '' ; 
    
END
$$


  
  


DROP PROCEDURE IF EXISTS sp_delete_pi_definition;
DELIMITER $$
CREATE PROCEDURE sp_delete_pi_definition()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete pi_definition started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM pi_definition
    WHERE is_ni = 0;

    SELECT CONCAT(@total,' pi_definition rows to delete.') AS '' ;


    
    DELETE FROM pi_definition
    WHERE is_ni = 0;

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' pi_definition rows deleted.') AS '';
    
    SELECT CONCAT('delete pi_definition finished at ',now()) AS '' ; 
    
END
$$


  
  

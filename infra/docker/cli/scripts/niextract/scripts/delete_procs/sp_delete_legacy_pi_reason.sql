
DROP PROCEDURE IF EXISTS sp_delete_legacy_pi_reason;
DELIMITER $$
CREATE PROCEDURE sp_delete_legacy_pi_reason()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete legacy_pi_reason started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM legacy_pi_reason
    WHERE is_ni = 0;

    SELECT CONCAT(@total,' legacy_pi_reason rows to delete.') AS '' ;


    
    DELETE FROM legacy_pi_reason
    WHERE is_ni = 0;

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' legacy_pi_reason rows deleted.') AS '';
    
    SELECT CONCAT('delete legacy_pi_reason finished at ',now()) AS '' ; 
    
END
$$


  
  

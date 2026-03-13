
DROP PROCEDURE IF EXISTS sp_delete_venue;
DELIMITER $$
CREATE PROCEDURE sp_delete_venue()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete venue started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
	INTO @total
	FROM venue 
    WHERE traffic_area_id <> 'N';

    SELECT CONCAT(@total,' venue rows to delete.') AS '' ;


    
    DELETE FROM venue
    WHERE traffic_area_id <> 'N'; 
    
    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' venue rows deleted.') AS '';
    
    SELECT CONCAT('delete venue finished at ',now()) AS '' ; 
    
END
$$


  
  

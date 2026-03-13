
DROP PROCEDURE IF EXISTS sp_delete_vehicle;
DELIMITER $$
CREATE PROCEDURE sp_delete_vehicle()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete vehicle started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM vehicle
    WHERE id NOT IN (
        SELECT vehicle_id
        FROM licence_vehicle);
                     
    SELECT CONCAT(@total,' vehicle rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO



        DELETE FROM vehicle
        WHERE id NOT IN (
            SELECT vehicle_id
            FROM licence_vehicle)
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' vehicle rows deleted.') AS '';

    END WHILE;
    
    SELECT CONCAT(@rowcount,' vehicle rows deleted.') AS '';
  
    set autocommit=1;

    SELECT CONCAT('delete vehicle finished at ',now()) AS '' ; 
    
END
$$


  
  

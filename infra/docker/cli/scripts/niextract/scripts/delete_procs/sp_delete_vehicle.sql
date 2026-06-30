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
    FROM vehicle v
    LEFT JOIN licence_vehicle lv ON v.id = lv.vehicle_id
    WHERE lv.vehicle_id IS NULL;
                     
    SELECT CONCAT(@total,' vehicle rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;
    
    WHILE(@rowcount = 10000) DO



        DELETE v FROM vehicle v
        LEFT JOIN licence_vehicle lv ON v.id = lv.vehicle_id
        WHERE lv.vehicle_id IS NULL
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' vehicle rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
  
    set autocommit=1;

    SELECT CONCAT('delete vehicle finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
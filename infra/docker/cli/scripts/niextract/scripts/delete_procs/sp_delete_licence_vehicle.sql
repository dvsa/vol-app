DROP PROCEDURE IF EXISTS sp_delete_licence_vehicle;
DELIMITER $$
CREATE PROCEDURE sp_delete_licence_vehicle()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete licence_vehicle started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM licence_vehicle lv    
    LEFT JOIN licence l ON lv.licence_id = l.id
    WHERE l.id IS NULL;
        
    SELECT CONCAT(@total,' licence_vehicle rows to delete.') AS '' ;
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO


    
        DELETE lv FROM licence_vehicle lv
        LEFT JOIN licence l ON lv.licence_id = l.id
        WHERE l.id IS NULL
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' licence_vehicle rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;
    
    SELECT CONCAT('delete licence_vehicle finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
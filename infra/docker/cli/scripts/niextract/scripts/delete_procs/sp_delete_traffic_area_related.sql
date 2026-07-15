DROP PROCEDURE IF EXISTS sp_delete_traffic_area_related;
DELIMITER $$
CREATE PROCEDURE sp_delete_traffic_area_related()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete traffic_area and related tables started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*) INTO @total FROM traffic_area WHERE id <> 'N';
    SELECT CONCAT(@total,' traffic_area rows to delete.') AS '';

    SET @rowcount := 10000;
    SET @total_deleted := 0;
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
        DELETE FROM traffic_area WHERE id <> 'N' LIMIT 10000;
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        SELECT CONCAT(@total_deleted,' traffic_area rows deleted.') AS '';
        COMMIT;
        START TRANSACTION;
    END WHILE;
    COMMIT;

    SELECT COUNT(*) INTO @total FROM recipient_traffic_area WHERE traffic_area_id <> 'N';
    SELECT CONCAT(@total,' recipient_traffic_area rows to delete.') AS '';

    SET @rowcount := 10000;
    SET @total_deleted := 0;
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
        DELETE FROM recipient_traffic_area WHERE traffic_area_id <> 'N' LIMIT 10000;
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        SELECT CONCAT(@total_deleted,' recipient_traffic_area rows deleted.') AS '';
        COMMIT;
        START TRANSACTION;
    END WHILE;
    COMMIT;

    SELECT COUNT(*) 
    INTO @total 
    FROM recipient r
    LEFT JOIN recipient_traffic_area rta ON r.id = rta.recipient_id
    WHERE rta.recipient_id IS NULL;
    
    SELECT CONCAT(@total,' recipient rows to delete.') AS '';
 
    SET @rowcount := 10000;
    SET @total_deleted := 0;
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
        DELETE r FROM recipient r
        LEFT JOIN recipient_traffic_area rta ON r.id = rta.recipient_id
        WHERE rta.recipient_id IS NULL
        LIMIT 10000;
        
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        SELECT CONCAT(@total_deleted,' recipient rows deleted.') AS '';
        COMMIT;
        START TRANSACTION;
    END WHILE;
    COMMIT;

    SELECT COUNT(*) INTO @total FROM continuation WHERE traffic_area_id <> 'N';
    SELECT CONCAT(@total,' continuation rows to delete.') AS '';
 
    SET @rowcount := 10000;
    SET @total_deleted := 0;
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
        DELETE FROM continuation WHERE traffic_area_id <> 'N' LIMIT 10000;
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        SELECT CONCAT(@total_deleted,' continuation rows deleted.') AS '';
        COMMIT;
        START TRANSACTION;
    END WHILE;
    COMMIT;

    SELECT COUNT(*) INTO @total FROM admin_area_traffic_area WHERE traffic_area_id <> 'N';
    SELECT CONCAT(@total,' admin_area_traffic_area rows to delete.') AS '';
 
    SET @rowcount := 10000;
    SET @total_deleted := 0;
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
        DELETE FROM admin_area_traffic_area WHERE traffic_area_id <> 'N' LIMIT 10000;
        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        SELECT CONCAT(@total_deleted,' admin_area_traffic_area rows deleted.') AS '';
        COMMIT;
        START TRANSACTION;
    END WHILE;
    COMMIT;

    SELECT CONCAT('delete traffic_area and related tables finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
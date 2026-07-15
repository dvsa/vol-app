DROP PROCEDURE IF EXISTS sp_delete_operating_centre;
DELIMITER $$
CREATE PROCEDURE sp_delete_operating_centre()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete operating_centre started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM operating_centre oc
    LEFT JOIN application_operating_centre aoc ON oc.id = aoc.operating_centre_id
    LEFT JOIN licence_operating_centre loc     ON oc.id = loc.operating_centre_id
    WHERE aoc.operating_centre_id IS NULL
      AND loc.operating_centre_id IS NULL;

    SELECT CONCAT(@total,' operating_centre rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO



        DELETE oc FROM operating_centre oc
        LEFT JOIN application_operating_centre aoc ON oc.id = aoc.operating_centre_id
        LEFT JOIN licence_operating_centre loc     ON oc.id = loc.operating_centre_id
        WHERE aoc.operating_centre_id IS NULL
          AND loc.operating_centre_id IS NULL
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' operating_centre rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;

    COMMIT;

    SELECT CONCAT('delete operating_centre finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
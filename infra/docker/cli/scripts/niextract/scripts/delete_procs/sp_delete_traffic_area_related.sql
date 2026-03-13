
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

    SELECT COUNT(*)
    INTO @total
    FROM traffic_area
    WHERE id <> 'N';

    SELECT CONCAT(@total,' traffic_area rows to delete.') AS '';


    
    DELETE FROM traffic_area
    WHERE id <> 'N';

    SET @rowcount := row_count();



    SELECT CONCAT(@rowcount,' traffic_area rows deleted.') AS '';

    SELECT COUNT(*)
    INTO @total
    FROM recipient_traffic_area
    WHERE traffic_area_id <> 'N';

    SELECT CONCAT(@total,' recipient_traffic_area rows to delete.') AS '';



    DELETE FROM recipient_traffic_area
    WHERE traffic_area_id <> 'N';

    SET @rowcount := row_count();



    SELECT CONCAT(@rowcount,' recipient_traffic_area rows deleted.') AS '';

    SELECT COUNT(*)
    INTO @total
    FROM recipient
    WHERE id NOT IN (
        SELECT recipient_id
        FROM recipient_traffic_area);

    SELECT CONCAT(@total,' recipient rows to delete.') AS '';
 


    DELETE FROM recipient
    WHERE id NOT IN (
        SELECT recipient_id
        FROM recipient_traffic_area);

    SET @rowcount := row_count();



    SELECT CONCAT(@rowcount,' recipient rows deleted.') AS '';

    SELECT COUNT(*)
    INTO @total
    FROM continuation
    WHERE traffic_area_id <> 'N';

    SELECT CONCAT(@total,' continuation rows to delete.') AS '';
 


    DELETE FROM continuation
    WHERE traffic_area_id <> 'N';

    SET @rowcount := row_count();



    SELECT CONCAT(@rowcount,' continuation rows deleted.') AS '';

    SELECT COUNT(*)
    INTO @total
    FROM admin_area_traffic_area
    WHERE traffic_area_id <> 'N';

    SELECT CONCAT(@total,' admin_area_traffic_area rows to delete.') AS '';
 


    DELETE FROM admin_area_traffic_area
    WHERE traffic_area_id <> 'N';

    SET @rowcount := row_count();



    SELECT CONCAT(@rowcount,' admin_area_traffic_area rows deleted.') AS '';


    SELECT CONCAT('delete traffic_area and related tables finished at ',now()) AS '' ; 
    
END
$$


  
  

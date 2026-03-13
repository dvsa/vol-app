
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
    FROM operating_centre
    WHERE id NOT IN (
        SELECT operating_centre_id
        FROM application_operating_centre)
    AND id NOT IN ( 
        SELECT operating_centre_id
        FROM licence_operating_centre);

    SELECT CONCAT(@total,' operating_centre rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO



        DELETE FROM operating_centre
        WHERE id NOT IN (
            SELECT operating_centre_id
            FROM application_operating_centre)
        AND id NOT IN ( 
            SELECT operating_centre_id
            FROM licence_operating_centre)
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' operating_centre rows deleted.') AS '';

    END WHILE;

    SELECT CONCAT('delete operating_centre finished at ',now()) AS '' ; 
    
END
$$


  
  

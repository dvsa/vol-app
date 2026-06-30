DROP PROCEDURE IF EXISTS sp_delete_phone_contact;
DELIMITER $$
CREATE PROCEDURE sp_delete_phone_contact()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete phone_contact started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM phone_contact pc
    LEFT JOIN contact_details cd ON pc.contact_details_id = cd.id
    WHERE cd.id IS NULL;
        
    SELECT CONCAT(@total,' phone_contact rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO


    
        DELETE pc FROM phone_contact pc
        LEFT JOIN contact_details cd ON pc.contact_details_id = cd.id
        WHERE cd.id IS NULL
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' phone_contact rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;

    COMMIT;

    SELECT CONCAT('delete phone_contact finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
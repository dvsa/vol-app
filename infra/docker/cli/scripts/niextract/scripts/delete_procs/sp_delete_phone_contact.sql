
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
    FROM phone_contact
    WHERE contact_details_id NOT IN (
        SELECT id
		FROM contact_details);
        
    SELECT CONCAT(@total,' phone_contact rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO


    
        DELETE FROM phone_contact
        WHERE contact_details_id NOT IN (
        SELECT id
		FROM contact_details)
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' phone_contact rows deleted.') AS '';

    END WHILE;

    SELECT CONCAT('delete phone_contact finished at ',now()) AS '' ; 
    
END
$$


  
  

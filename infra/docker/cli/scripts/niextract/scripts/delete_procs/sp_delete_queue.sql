
DROP PROCEDURE IF EXISTS sp_delete_queue;
DELIMITER $$
CREATE PROCEDURE sp_delete_queue()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete queue started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM queue;

    SELECT CONCAT(@total,' queue rows to delete.') AS '' ;



    TRUNCATE TABLE queue;
    

    
    SELECT CONCAT(@total,' queue rows deleted.') AS '';

    SELECT CONCAT('delete queue finished at ',now()) AS '' ; 
    
END
$$


  
  

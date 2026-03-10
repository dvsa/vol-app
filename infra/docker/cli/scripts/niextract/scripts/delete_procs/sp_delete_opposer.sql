
DROP PROCEDURE IF EXISTS sp_delete_opposer;
DELIMITER $$
CREATE PROCEDURE sp_delete_opposer()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete opposer started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM opposer
    WHERE id NOT IN (
        SELECT opposer_id
        FROM opposition);

    SELECT CONCAT(@total,' opposer rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO



        DELETE FROM opposer
        WHERE id NOT IN (
        SELECT opposer_id
        FROM opposition)
        LIMIT 10000;
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' opposer rows deleted.') AS '';

    END WHILE;

    SELECT CONCAT('delete opposer finished at ',now()) AS '' ; 
    
END
$$


  
  

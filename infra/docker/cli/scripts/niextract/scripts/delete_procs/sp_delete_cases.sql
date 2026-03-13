
DROP PROCEDURE IF EXISTS sp_delete_cases;
DELIMITER $$
CREATE PROCEDURE sp_delete_cases()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete cases started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM cases
    WHERE application_id IS NULL
    AND transport_manager_id IS NULL
    AND licence_id IS NULL;

    SELECT CONCAT(@total,' case rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;

    WHILE(@rowcount = 10000) DO



        DELETE FROM cases
        WHERE application_id IS NULL
        AND transport_manager_id IS NULL
        AND licence_id IS NULL
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' case rows deleted') AS '';

    END WHILE;

    set autocommit=1;

    SELECT CONCAT('delete cases finished at ',now()) AS '' ; 
    
END
$$


  
  

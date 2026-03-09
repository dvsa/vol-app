
DROP PROCEDURE IF EXISTS sp_delete_licence;
DELIMITER $$
CREATE PROCEDURE sp_delete_licence()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete licence started at ',now()) AS '' ; 
    
    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
	INTO @total
	FROM licence 
    WHERE traffic_area_id <> 'N' ;

    SELECT CONCAT(@total,' licence rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;
    WHILE(@rowcount = 10000) DO



        DELETE FROM licence 
        WHERE traffic_area_id <> 'N'
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
        
        SELECT CONCAT(@total,' licence rows deleted') AS '';



    END WHILE;        

    set autocommit=1;

    SELECT CONCAT('delete licence finished at ',now()) AS '' ; 
    
END
$$


  
  


DROP PROCEDURE IF EXISTS sp_delete_companies_house_company;
DELIMITER $$
CREATE PROCEDURE sp_delete_companies_house_company()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete companies_house_company started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM companies_house_company;

    SELECT CONCAT(@total,' companies_house_company rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO


    
        DELETE FROM companies_house_company
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' companies_house_company rows deleted.') AS '';

    END WHILE;


    SELECT CONCAT('delete companies_house_company finished at ',now()) AS '' ; 
    
END
$$


  
  

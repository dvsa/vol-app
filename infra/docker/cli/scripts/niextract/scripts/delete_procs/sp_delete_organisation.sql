DROP PROCEDURE IF EXISTS sp_delete_organisation;
DELIMITER $$
CREATE PROCEDURE sp_delete_organisation()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete organisation started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SELECT COUNT(*)
    INTO @total
    FROM organisation
    WHERE id NOT IN ( 
        SELECT organisation_id
	FROM licence);

    SELECT CONCAT(@total,' organisation rows to delete.') AS '' ;

    SET @total:=0;
    SET @rowcount:=10000;

    WHILE(@rowcount = 10000) DO


    
        DELETE FROM organisation
        WHERE id NOT IN (
            SELECT organisation_id
	    FROM licence)
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    


    END WHILE;
    
    SELECT CONCAT(@total,' organisation rows deleted.') AS '';
  
    SELECT CONCAT('delete organisation finished at ',now()) AS '' ; 
    
END
$$

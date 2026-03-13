
DROP PROCEDURE IF EXISTS sp_delete_disqualification_2;
DELIMITER $$
CREATE PROCEDURE sp_delete_disqualification_2()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete disqualification started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM disqualification
    WHERE person_id NOT IN (
        SELECT id
        FROM person)
	OR organisation_id NOT IN (
        SELECT id
        FROM organisation);

    SELECT CONCAT(@total,' disqualification rows to delete.') AS '' ;


    
    DELETE FROM disqualification
    WHERE person_id NOT IN (
        SELECT id
        FROM person)
	OR organisation_id NOT IN (
        SELECT id
        FROM organisation);
  
    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' disqualification rows deleted.') AS '';

    SELECT CONCAT('delete disqualification finished at ',now()) AS '' ; 
    
END
$$


  
  

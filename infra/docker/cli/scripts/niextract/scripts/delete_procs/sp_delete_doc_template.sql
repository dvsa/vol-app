
DROP PROCEDURE IF EXISTS sp_delete_doc_template;
DELIMITER $$
CREATE PROCEDURE sp_delete_doc_template()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete doc_template started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
    INTO @total
    FROM doc_template
    WHERE is_ni = 0;

    SELECT CONCAT(@total,' doc_template rows to delete.') AS '' ;


    
    DELETE FROM doc_template
    WHERE is_ni = 0;

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@rowcount,' doc_template rows deleted.') AS '';
    
    SELECT CONCAT('delete doc_template finished at ',now()) AS '' ; 
    
END
$$


  
  

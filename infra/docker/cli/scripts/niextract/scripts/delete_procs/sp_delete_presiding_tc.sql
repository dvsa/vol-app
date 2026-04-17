
DROP PROCEDURE IF EXISTS sp_delete_presiding_tc;
DELIMITER $$
CREATE PROCEDURE sp_delete_presiding_tc()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete presiding_tc started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM presiding_tc
    WHERE id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM hearing)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM impounding)
    AND id NOT IN (SELECT ifnull(agreed_by_tc_id,0) FROM pi)
    AND id NOT IN (SELECT ifnull(decided_by_tc_id,0) FROM pi)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM pi_hearing)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM propose_to_revoke);

    SELECT CONCAT(@total,' presiding_tc rows to delete.') AS '' ;
 

    
    DELETE FROM presiding_tc
    WHERE id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM hearing)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM impounding)
    AND id NOT IN (SELECT ifnull(agreed_by_tc_id,0) FROM pi)
    AND id NOT IN (SELECT ifnull(decided_by_tc_id,0) FROM pi)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM pi_hearing)
    AND id NOT IN (SELECT ifnull(presiding_tc_id,0) FROM propose_to_revoke);

    SET @rowcount := row_count();
    

    
    SELECT CONCAT(@total,' presiding_tc rows deleted.') AS '';

    SELECT CONCAT('delete presiding_tc finished at ',now()) AS '' ; 
    
END
$$


  
  

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
    FROM presiding_tc pt
    LEFT JOIN hearing h         ON pt.id = h.presiding_tc_id
    LEFT JOIN impounding i      ON pt.id = i.presiding_tc_id
    LEFT JOIN pi p1             ON pt.id = p1.agreed_by_tc_id
    LEFT JOIN pi p2             ON pt.id = p2.decided_by_tc_id
    LEFT JOIN pi_hearing ph     ON pt.id = ph.presiding_tc_id
    LEFT JOIN propose_to_revoke ptr ON pt.id = ptr.presiding_tc_id
    WHERE h.presiding_tc_id IS NULL
      AND i.presiding_tc_id IS NULL
      AND p1.agreed_by_tc_id IS NULL
      AND p2.decided_by_tc_id IS NULL
      AND ph.presiding_tc_id IS NULL
      AND ptr.presiding_tc_id IS NULL;

    SELECT CONCAT(@total,' presiding_tc rows to delete.') AS '' ;

    SET @total_deleted := 0;
    SET @rowcount := 10000;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO
    
        DELETE pt FROM presiding_tc pt
        LEFT JOIN hearing h         ON pt.id = h.presiding_tc_id
        LEFT JOIN impounding i      ON pt.id = i.presiding_tc_id
        LEFT JOIN pi p1             ON pt.id = p1.agreed_by_tc_id
        LEFT JOIN pi p2             ON pt.id = p2.decided_by_tc_id
        LEFT JOIN pi_hearing ph     ON pt.id = ph.presiding_tc_id
        LEFT JOIN propose_to_revoke ptr ON pt.id = ptr.presiding_tc_id
        WHERE h.presiding_tc_id IS NULL
          AND i.presiding_tc_id IS NULL
          AND p1.agreed_by_tc_id IS NULL
          AND p2.decided_by_tc_id IS NULL
          AND ph.presiding_tc_id IS NULL
          AND ptr.presiding_tc_id IS NULL
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
    
        SELECT CONCAT(@total_deleted,' presiding_tc rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;
    
    COMMIT;

    SELECT CONCAT('delete presiding_tc finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
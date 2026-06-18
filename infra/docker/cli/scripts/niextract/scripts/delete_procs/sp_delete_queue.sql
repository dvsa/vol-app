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

    /*
      TRUNCATE is a DDL command that implicitly commits active transactions 
      and cannot be rolled back by the EXIT HANDLER. Swapped to a batched 
      DELETE loop to safely honor transactional boundaries and rollback safety.
    */
    SET @rowcount := 10000;
    SET @total_deleted := 0;

    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        DELETE FROM queue
        LIMIT 10000;

        SET @rowcount := row_count();
        SET @total_deleted := @total_deleted + @rowcount;
        
        SELECT CONCAT(@total_deleted,' queue rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;

    COMMIT;

    SELECT CONCAT('delete queue finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;

DROP PROCEDURE IF EXISTS sp_delete_txn;
DELIMITER $$
CREATE PROCEDURE sp_delete_txn()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete txn started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT COUNT(*)
	INTO @total
    FROM txn
    WHERE id NOT IN (
        SELECT txn_id
        FROM fee_txn);

    SELECT CONCAT(@total,' txn rows to delete.') AS '';

    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO


         
        DELETE FROM txn
        WHERE id NOT IN (
            SELECT txn_id
            FROM fee_txn);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;



        SELECT CONCAT(@total,' txn rows deleted.') AS '';

    END WHILE;
    
    SELECT CONCAT(@rowcount,' txn rows deleted.') AS '';
  
    set autocommit=1;

    SELECT CONCAT('delete txn finished at ',now()) AS '' ; 
    
END
$$


  
  

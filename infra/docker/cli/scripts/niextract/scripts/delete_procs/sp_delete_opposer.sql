DROP PROCEDURE IF EXISTS sp_delete_opposer;
DELIMITER $$
CREATE PROCEDURE sp_delete_opposer()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete opposer started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;

    SELECT COUNT(*)
    INTO @total
    FROM opposer o
    LEFT JOIN opposition op ON o.id = op.opposer_id
    WHERE op.opposer_id IS NULL;

    SELECT CONCAT(@total,' opposer rows to delete.') AS ''; 
    
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        DELETE FROM opposer
        WHERE id IN (
            SELECT id FROM (
                SELECT o.id
                FROM opposer o
                LEFT JOIN opposition op ON o.id = op.opposer_id
                WHERE op.opposer_id IS NULL
                LIMIT 10000
            ) AS batch
        );
    
        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    

    
        SELECT CONCAT(@total,' opposer rows deleted.') AS '';

        COMMIT;
        START TRANSACTION;

    END WHILE;

    COMMIT;

    SELECT CONCAT('delete opposer finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
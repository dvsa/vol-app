DROP PROCEDURE IF EXISTS sp_delete_transport_manager;
DELIMITER $$
CREATE PROCEDURE sp_delete_transport_manager()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete transport_manager started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=1;
    #------------------------
  
    SET autocommit=0;
    
    SELECT 'Identifying address rows to delete.' AS '' ;

    DROP TABLE IF EXISTS `tmpTM`;
    CREATE TEMPORARY TABLE `tmpTM` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );
    
    INSERT INTO tmpTM (id)
    SELECT tm.id 
    FROM transport_manager tm
    LEFT JOIN user u ON tm.id = u.transport_manager_id
    LEFT JOIN transport_manager_licence tml ON tm.id = tml.transport_manager_id
    LEFT JOIN transport_manager_application tma ON tm.id = tma.transport_manager_id
    WHERE u.transport_manager_id IS NULL
      AND tml.transport_manager_id IS NULL
      AND tma.transport_manager_id IS NULL;

    SELECT COUNT(*)
    INTO @total
    FROM tmpTM;

    SELECT CONCAT(@total,' transport_manager rows to delete.') AS '' ;

    DROP TABLE IF EXISTS `tmpTMBatch`;
    CREATE TEMPORARY TABLE `tmpTMBatch` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );

    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        INSERT INTO tmpTMBatch (id)
        SELECT id FROM tmpTM
        LIMIT 10000;
        
        DELETE FROM transport_manager
        WHERE id IN (SELECT id FROM tmpTMBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpTM
        LIMIT 10000;
        
        SELECT CONCAT(@total,' transport_manager rows deleted.') AS '';

        TRUNCATE TABLE tmpTMBatch;
        
        COMMIT;
        START TRANSACTION;
        
    END WHILE;

    COMMIT;

    SET autocommit=1;

    SELECT CONCAT('delete transport_manager finished at ',now()) AS '' ; 
    
    DROP TABLE IF EXISTS `tmpTM`;
    DROP TABLE IF EXISTS `tmpTMBatch`;
    
END
$$
DELIMITER ;
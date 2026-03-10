
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
    
   
    DROP TABLE IF EXISTS `tmpTM`;
    
    CREATE TEMPORARY TABLE `tmpTM`
    AS SELECT id from transport_manager;

    SELECT 'Identifying address rows to delete.' AS '' ;
    
    DELETE FROM tmpTM
    WHERE id IN (SELECT DISTINCT transport_manager_id
                 FROM user
                 WHERE transport_manager_id is not null);
                 
    DELETE FROM tmpTM
    WHERE id IN (SELECT DISTINCT transport_manager_id
                 FROM transport_manager_licence
                 WHERE transport_manager_id is not null);             

    DELETE FROM tmpTM
    WHERE id IN (SELECT DISTINCT transport_manager_id
                 FROM transport_manager_application
                 WHERE transport_manager_id is not null);     

    
    SELECT COUNT(*)
    INTO @total
	FROM tmpTM;

    SELECT CONCAT(@total,' transport_manager rows to delete.') AS '' ;

    DROP TABLE IF EXISTS `tmpTMBatch`;
    CREATE TEMPORARY TABLE `tmpTMBatch` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );

    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO

        INSERT tmpTMBatch
        SELECT id FROM tmpTM
        LIMIT 10000;
        

    
       DELETE FROM transport_manager
       WHERE id IN ( select id FROM tmpTMBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpTM
        WHERE id IN ( SELECT id from tmpTMBatch);
        

    
        SELECT CONCAT(@total,' transport_manager rows deleted.') AS '';

        truncate table tmpTMBatch;
        
    END WHILE;

    set autocommit=1;

    SELECT CONCAT('delete transport_manager finished at ',now()) AS '' ; 
    
    DROP TABLE IF EXISTS `tmpTM`;
    DROP TABLE IF EXISTS `tmpTMBatch`;
    
END
$$


  
  

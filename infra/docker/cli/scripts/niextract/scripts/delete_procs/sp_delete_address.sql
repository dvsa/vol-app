DROP PROCEDURE IF EXISTS sp_delete_address;
DELIMITER $$
CREATE PROCEDURE sp_delete_address()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete address started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    DROP TABLE IF EXISTS `tmpAddress`;
    CREATE TEMPORARY TABLE `tmpAddress` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );

    SELECT 'Identifying address rows to delete.' AS '' ;
    
    INSERT INTO tmpAddress (id)
    SELECT a.id 
    FROM address a
    LEFT JOIN contact_details cd ON a.id = cd.address_id
    LEFT JOIN operating_centre oc ON a.id = oc.address_id
    LEFT JOIN venue v             ON a.id = v.address_id
    WHERE cd.address_id IS NULL
      AND oc.address_id IS NULL
      AND v.address_id IS NULL;
    
    SELECT COUNT(*)
    INTO @total
    FROM tmpAddress;

    SELECT CONCAT(@total,' address rows to delete.') AS '' ;

    DROP TABLE IF EXISTS `tmpAddressBatch`;
    CREATE TEMPORARY TABLE `tmpAddressBatch` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );

    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        INSERT INTO tmpAddressBatch (id)
        SELECT id FROM tmpAddress
        LIMIT 10000;
        
        DELETE FROM address
        WHERE id IN (SELECT id FROM tmpAddressBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpAddress
        WHERE id IN (SELECT id FROM tmpAddressBatch);
        
        SELECT CONCAT(@total,' address rows deleted.') AS '';

        TRUNCATE TABLE tmpAddressBatch;
        
        COMMIT;
        START TRANSACTION;
        
    END WHILE;

    COMMIT;

    DROP TABLE IF EXISTS `tmpAddress`;
    DROP TABLE IF EXISTS `tmpAddressBatch`;

    SELECT CONCAT('delete address finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
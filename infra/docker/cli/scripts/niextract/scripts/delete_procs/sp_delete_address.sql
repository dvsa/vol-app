
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
    PRIMARY KEY (`id`) );

    SELECT 'Identifying address rows to delete.' AS '' ;
    
    INSERT INTO tmpAddress (id)
    select id from address;
    
    DELETE FROM tmpAddress
    WHERE id IN (SELECT DISTINCT address_id FROM contact_details);
    
    DELETE FROM tmpAddress
    WHERE id IN (SELECT DISTINCT address_id FROM operating_centre);
    
    DELETE FROM tmpAddress
    WHERE id IN (SELECT DISTINCT address_id FROM venue);
    
    SELECT COUNT(*)
    INTO @total
	FROM tmpAddress;

    SELECT CONCAT(@total,' address rows to delete.') AS '' ;

    DROP TABLE IF EXISTS `tmpAddressBatch`;
    CREATE TEMPORARY TABLE `tmpAddressBatch` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );

    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO

        INSERT tmpAddressBatch
        SELECT id FROM  tmpAddress
        LIMIT 10000;
        

    
        DELETE FROM address
        WHERE id IN ( SELECT id from tmpAddressBatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpAddress
        WHERE id IN ( SELECT id from tmpAddressBatch);
        

    
        SELECT CONCAT(@total,' address rows deleted.') AS '';

        truncate table tmpAddressBatch;
        
    END WHILE;

    DROP TABLE IF EXISTS `tmpAddress`;
    DROP TABLE IF EXISTS `tmpAddressBatch`;

    SELECT CONCAT('delete address finished at ',now()) AS '' ; 
    
END
$$


  
  

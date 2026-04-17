
DROP PROCEDURE IF EXISTS sp_delete_contact_details;
DELIMITER $$
CREATE PROCEDURE sp_delete_contact_details()
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT('delete contact_details started at ',now()) AS '' ; 

    #------------------------    
    SET FOREIGN_KEY_CHECKS=0;
    #------------------------
  
    SET autocommit=0;

    SELECT 'Identifying contact_details rows to delete.' AS '' ;
    
    DROP TABLE IF EXISTS `tmpCD`;
    CREATE TEMPORARY TABLE `tmpCD` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );
  
    INSERT INTO tmpCD (id)
    select id from contact_details;

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT complainant_contact_details_id FROM complaint);
 
    DELETE FROM tmpCD  
    WHERE id IN (SELECT DISTINCT correspondence_cd_id FROM licence);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT establishment_cd_id FROM licence);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT transport_consultant_cd_id FROM licence);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM opposer);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM organisation);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT irfo_contact_details_id FROM organisation);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM private_hire_licence);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT requestors_contact_details_id FROM statement);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM tm_employment);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM traffic_area);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT home_cd_id FROM transport_manager);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT work_cd_id FROM transport_manager);

    DELETE FROM tmpCD
    WHERE id IN (SELECT DISTINCT contact_details_id FROM workshop);

    DROP TABLE IF EXISTS `tmpCDbatch`;
    CREATE TEMPORARY TABLE `tmpCDbatch` ( 
    `id` int(10) unsigned NOT NULL,
    PRIMARY KEY (`id`) );

    SELECT COUNT(*)
    INTO @total
    FROM tmpCD;

    SELECT CONCAT(@total,' contact_details rows to delete.') AS '' ;
  
    SET @total:=0;
    SET @rowcount:=10000;
    
    WHILE(@rowcount = 10000) DO

        INSERT tmpCDbatch
        SELECT id FROM  tmpCD
        LIMIT 10000;
        

    
        DELETE FROM contact_details
        WHERE id IN ( SELECT id from tmpCDbatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpCD
        WHERE id IN ( SELECT id from tmpCDbatch);
        

    
        SELECT CONCAT(@total,' contact_details rows deleted.') AS '';

        truncate table tmpCDbatch;
        
    END WHILE;

    DROP TABLE IF EXISTS `tmpCD`;
    DROP TABLE IF EXISTS `tmpCDbatch`;

    SELECT CONCAT('delete contact_details finished at ',now()) AS '' ; 
    
END
$$


  
  

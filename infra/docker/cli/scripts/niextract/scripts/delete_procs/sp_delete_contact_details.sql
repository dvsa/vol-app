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
        PRIMARY KEY (`id`) 
    );
  
    INSERT INTO tmpCD (id)
    SELECT c.id 
    FROM contact_details c
    LEFT JOIN complaint comp               ON c.id = comp.complainant_contact_details_id
    LEFT JOIN licence l1                   ON c.id = l1.correspondence_cd_id
    LEFT JOIN licence l2                   ON c.id = l2.establishment_cd_id
    LEFT JOIN licence l3                   ON c.id = l3.transport_consultant_cd_id
    LEFT JOIN opposer o                    ON c.id = o.contact_details_id
    LEFT JOIN organisation org1            ON c.id = org1.contact_details_id
    LEFT JOIN organisation org2            ON c.id = org2.irfo_contact_details_id
    LEFT JOIN private_hire_licence phl     ON c.id = phl.contact_details_id
    LEFT JOIN statement s                  ON c.id = s.requestors_contact_details_id
    LEFT JOIN tm_employment tme            ON c.id = tme.contact_details_id
    LEFT JOIN traffic_area ta              ON c.id = ta.contact_details_id
    LEFT JOIN transport_manager tm1        ON c.id = tm1.home_cd_id
    LEFT JOIN transport_manager tm2        ON c.id = tm2.work_cd_id
    LEFT JOIN workshop w                   ON c.id = w.contact_details_id
    WHERE comp.complainant_contact_details_id IS NULL
      AND l1.correspondence_cd_id IS NULL
      AND l2.establishment_cd_id IS NULL
      AND l3.transport_consultant_cd_id IS NULL
      AND o.contact_details_id IS NULL
      AND org1.contact_details_id IS NULL
      AND org2.irfo_contact_details_id IS NULL
      AND phl.contact_details_id IS NULL
      AND s.requestors_contact_details_id IS NULL
      AND tme.contact_details_id IS NULL
      AND ta.contact_details_id IS NULL
      AND tm1.home_cd_id IS NULL
      AND tm2.work_cd_id IS NULL
      AND w.contact_details_id IS NULL;

    DROP TABLE IF EXISTS `tmpCDbatch`;
    CREATE TEMPORARY TABLE `tmpCDbatch` ( 
        `id` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id`) 
    );

    SELECT COUNT(*)
    INTO @total
    FROM tmpCD;

    SELECT CONCAT(@total,' contact_details rows to delete.') AS '' ;
  
    SET @total:=0;
    SET @rowcount:=10000;
    
    START TRANSACTION;

    WHILE(@rowcount = 10000) DO

        INSERT INTO tmpCDbatch (id)
        SELECT id FROM tmpCD
        LIMIT 10000;
        
        DELETE FROM contact_details
        WHERE id IN (SELECT id FROM tmpCDbatch);

        SET @rowcount := row_count();
        SET @total := @total + @rowcount;
    
        DELETE FROM tmpCD
        WHERE id IN (SELECT id FROM tmpCDbatch);
        
        SELECT CONCAT(@total,' contact_details rows deleted.') AS '';

        TRUNCATE TABLE tmpCDbatch;
        
        COMMIT;
        START TRANSACTION;
        
    END WHILE;

    COMMIT;

    DROP TABLE IF EXISTS `tmpCD`;
    DROP TABLE IF EXISTS `tmpCDbatch`;

    SELECT CONCAT('delete contact_details finished at ',now()) AS '' ; 
    
END
$$
DELIMITER ;
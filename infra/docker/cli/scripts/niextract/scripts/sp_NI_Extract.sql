
DROP PROCEDURE IF EXISTS sp_NI_Extract;
DELIMITER $$
CREATE PROCEDURE sp_NI_Extract(continue_extract int)
BEGIN

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @sqlstate = RETURNED_SQLSTATE,
        @errno = MYSQL_ERRNO, @text = MESSAGE_TEXT;
        ROLLBACK;
        SET @full_error = CONCAT("ERROR ", @errno, " (", @sqlstate, "): ", @text);
        SELECT CONCAT("NI Extract Failed: ",@full_error) AS '';
    END;

    SELECT CONCAT(now(),' NI Extract started...') AS '' ; 

    # save table count on first run, ignore if continuing existing run.

    IF continue_extract = 0 THEN
        CALL sp_NI_Extract_save_table_counts;
    END IF;

    CALL sp_delete_licence;
  
    CALL sp_delete_transport_manager;

    CALL sp_delete_cases;
    
    CALL sp_delete_fees;
    
    CALL sp_delete_organisation;

    CALL sp_delete_vehicle;
    
    CALL sp_delete_txn;

    CALL sp_delete_trading_name;

    CALL sp_delete_decision;

    CALL sp_delete_disqualification;

    CALL sp_delete_doc_template;

    CALL sp_delete_enforcement_area;

    CALL sp_delete_legacy_pi_reason;

    CALL sp_delete_operating_centre;
    
    CALL sp_delete_opposer;

    CALL sp_delete_pi_definition;

    CALL sp_delete_queue;
    
    CALL sp_delete_reason;
    
    CALL sp_delete_scan;
    
    CALL sp_delete_venue;

    CALL sp_delete_contact_details;

    CALL sp_delete_phone_contact;

    CALL sp_delete_person;

    CALL sp_delete_disqualification_2;

    CALL sp_delete_address;
    
    CALL sp_delete_traffic_area_related;
    
    CALL sp_delete_companies_house_company;

    CALL sp_delete_presiding_tc;
    
    CALL sp_delete_bus_reg;
    
    set autocommit=1;

    SELECT 'Updating final table counts...' AS '' ; 

    CALL sp_NI_Extract_update_table_counts;

    SELECT * FROM NI_Extract;

    SELECT CONCAT(now(),' ...NI Extract completed OK') AS '' ; 
    
END
$$

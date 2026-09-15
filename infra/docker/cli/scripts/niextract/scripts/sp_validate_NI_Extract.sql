DROP PROCEDURE IF EXISTS sp_validate_NI_Extract;
DELIMITER $$
CREATE PROCEDURE sp_validate_NI_Extract()
BEGIN

# NI extract data integrity validation report

SELECT '' AS '';
SELECT '-------------------------------------------' AS '';
SELECT 'NI extract data integrity validation report' AS '';
SELECT '-------------------------------------------' AS '';

# licence

SELECT '-- licence --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(o.id IS NOT NULL, 1, 0)),
    SUM(IF(d1.id IS NOT NULL, 1, 0)),
    SUM(IF(l.correspondence_cd_id IS NULL, 1, 0)),
    SUM(IF(d2.id IS NOT NULL, 1, 0)),
    SUM(IF(l.transport_consultant_cd_id IS NULL, 1, 0)),
    SUM(IF(d3.id IS NOT NULL, 1, 0)),
    SUM(IF(l.establishment_cd_id IS NULL, 1, 0)),
    SUM(IF(e.id IS NOT NULL, 1, 0)),
    SUM(IF(l.enforcement_area_id IS NULL, 1, 0)),
    SUM(IF(t.id IS NOT NULL, 1, 0)),
    SUM(IF(l.traffic_area_id IS NULL, 1, 0))
INTO 
    @total, @total_org_fk, @total_corr_fk, @total_null_corr,
    @total_tc_fk, @total_null_tc, @total_est_fk, @total_null_est,
    @total_enf_fk, @total_null_enf, @total_tra_fk, @total_null_tra
FROM licence l
LEFT JOIN organisation o ON l.organisation_id = o.id
LEFT JOIN contact_details d1 ON l.correspondence_cd_id = d1.id
LEFT JOIN contact_details d2 ON l.transport_consultant_cd_id = d2.id
LEFT JOIN contact_details d3 ON l.establishment_cd_id = d3.id
LEFT JOIN enforcement_area e ON l.enforcement_area_id = e.id
LEFT JOIN traffic_area t ON l.traffic_area_id = t.id;

SELECT IF(@total_org_fk = @total, "licence -> organisation FK check OK", "licence -> organisation FK check FAILED!") AS ''; 
SELECT IF((@total_corr_fk + @total_null_corr) = @total, "licence -> contact_details correspondence_cd_id FK check OK", "licence -> contact_details correspondence_cd_id FK check FAILED!") AS ''; 
SELECT IF((@total_tc_fk + @total_null_tc) = @total, "licence -> contact_details transport_consultant_cd_id FK check OK", "licence -> contact_details transport_consultant_cd_id FK check FAILED!") AS ''; 
SELECT IF((@total_est_fk + @total_null_est) = @total, "licence -> contact_details establishment_cd_id FK check OK", "licence -> contact_details establishment_cd_id FK check FAILED!") AS ''; 
SELECT IF((@total_enf_fk + @total_null_enf) = @total, "licence -> enforcement_area FK check OK", "licence -> enforcement_area FK check FAILED!") AS ''; 
SELECT IF((@total_tra_fk + @total_null_tra) = @total, "licence -> traffic_area FK check OK", "licence -> traffic_area FK check FAILED!") AS ''; 
   
# organisation

select '-- organisation --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(d1.id IS NOT NULL, 1, 0)),
    SUM(IF(o.contact_details_id IS NULL, 1, 0)),
    SUM(IF(d2.id IS NOT NULL, 1, 0)),
    SUM(IF(o.irfo_contact_details_id IS NULL, 1, 0))
INTO @total, @total_cd_fk, @total_null_cd, @total_irfo_cd_fk, @total_null_irfo_cd
FROM organisation o
LEFT JOIN contact_details d1 ON o.contact_details_id = d1.id
LEFT JOIN contact_details d2 ON o.irfo_contact_details_id = d2.id;

SELECT IF((@total_cd_fk + @total_null_cd) = @total, "organisation -> contact_details contact_details FK check OK", "organisation -> contact_details contact_details FK check FAILED!") AS ''; 
SELECT IF((@total_irfo_cd_fk + @total_null_irfo_cd) = @total, "organisation -> contact_details irfo_contact_details_id FK check OK", "organisation -> contact_details irfo_contact_details_id FK check FAILED!") AS ''; 

# contact_details

SELECT '-- contact_details --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(p.id IS NOT NULL, 1, 0)),
    SUM(IF(c.person_id IS NULL, 1, 0)),
    SUM(IF(a.id IS NOT NULL, 1, 0)),
    SUM(IF(c.address_id IS NULL, 1, 0))
INTO @total, @total_person_fk, @total_null_person, @total_address_fk, @total_null_address
FROM contact_details c
LEFT JOIN person p ON c.person_id = p.id
LEFT JOIN address a ON c.address_id = a.id;

SELECT IF((@total_person_fk + @total_null_person) = @total, "contact_details -> person FK check OK", "contact_details -> person FK check FAILED!") AS ''; 
SELECT IF((@total_address_fk + @total_null_address) = @total, "contact_details -> address FK check OK", "contact_details -> address FK check FAILED!") AS ''; 

# address - has no FKs of its own
# check tables that have address as a FK

# operating_centre

SELECT '-- operating_centre --' AS '';
 
SELECT 
    COUNT(*),
    SUM(IF(a.id IS NOT NULL, 1, 0)),
    SUM(IF(o.address_id IS NULL, 1, 0))
INTO @total, @total_address_fk, @total_null_address
FROM operating_centre o
LEFT JOIN address a ON o.address_id = a.id;

SELECT IF((@total_address_fk + @total_null_address) = @total, "operating_centre -> address FK check OK", "operating_centre -> address FK check FAILED!") AS ''; 

# venue

SELECT '-- venue --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(a.id IS NOT NULL, 1, 0)),
    SUM(IF(v.address_id IS NULL, 1, 0))
INTO @total, @total_address_fk, @total_null_address
FROM venue v
LEFT JOIN address a ON v.address_id = a.id;

SELECT IF((@total_address_fk + @total_null_address) = @total, "venue -> address FK check OK", "venue -> address FK check FAILED!") AS ''; 

# person - has no FKs of its own
# check tables that have person as a FK

# application_organisation_person

SELECT '-- application_organisation_person --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(p.id IS NOT NULL, 1, 0))
INTO @total, @total_person_fk
FROM application_organisation_person a
LEFT JOIN person p ON a.person_id = p.id;

SELECT IF(@total_person_fk = @total, "application_organisation_person -> person FK check OK", "application_organisation_person -> person FK check FAILED!") AS ''; 

# organisation_person

SELECT '-- organisation_person --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(p.id IS NOT NULL, 1, 0)),
    SUM(IF(o.id IS NOT NULL, 1, 0))
INTO @total, @total_person_fk, @total_org_fk
FROM organisation_person c
LEFT JOIN person p ON c.person_id = p.id
LEFT JOIN organisation o ON c.organisation_id = o.id;

SELECT IF(@total_person_fk = @total, "organisation_person -> person FK check OK", "organisation_person -> person FK check FAILED!") AS ''; 
SELECT IF(@total_org_fk = @total, "organisation_person -> organisation FK check OK", "organisation_person -> organisation FK check FAILED!") AS ''; 

# phone_contact

SELECT '-- phone_contact --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(c.id IS NOT NULL, 1, 0))
INTO @total, @total_cd_fk
FROM phone_contact p
LEFT JOIN contact_details c ON p.contact_details_id = c.id;

SELECT IF(@total_cd_fk = @total, "phone_contact -> contact_details FK check OK", "phone_contact -> contact_details FK check FAILED!") AS '';
 
# trading_name

SELECT '-- trading_name --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(c.id IS NOT NULL, 1, 0)),
    SUM(IF(t.licence_id IS NULL, 1, 0)),
    SUM(IF(o.id IS NOT NULL, 1, 0)),
    SUM(IF(t.organisation_id IS NULL, 1, 0))
INTO @total, @total_lic_fk, @total_null_lic, @total_org_fk, @total_null_org
FROM trading_name t
LEFT JOIN licence c ON t.licence_id = c.id
LEFT JOIN organisation o ON t.organisation_id = o.id;

SELECT IF((@total_lic_fk + @total_null_lic) = @total, "trading_name -> licence FK check OK", "trading_name -> licence FK check FAILED!") AS '';
SELECT IF((@total_org_fk + @total_null_org) = @total, "trading_name -> organisation FK check OK", "trading_name -> organisation FK check FAILED!") AS '';
 
# vehicle - no fks

SELECT '-- licence_vehicle --' AS '';

# licence_vehicle

SELECT 
    COUNT(*),
    SUM(IF(l.id IS NOT NULL, 1, 0)),
    SUM(IF(v.id IS NOT NULL, 1, 0)),
    SUM(IF(a1.id IS NOT NULL, 1, 0)),
    SUM(IF(lv.application_id IS NULL, 1, 0)),
    SUM(IF(a2.id IS NOT NULL, 1, 0)),
    SUM(IF(lv.interim_application_id IS NULL, 1, 0))
INTO 
    @total, @total_lic_fk, @total_veh_fk, @total_app_fk, 
    @total_null_app, @total_int_app_fk, @total_null_int_app
FROM licence_vehicle lv
LEFT JOIN licence l ON lv.licence_id = l.id
LEFT JOIN vehicle v ON lv.vehicle_id = v.id
LEFT JOIN application a1 ON lv.application_id = a1.id
LEFT JOIN application a2 ON lv.interim_application_id = a2.id;

SELECT IF(@total_lic_fk = @total, "licence_vehicle -> licence FK check OK", "licence_vehicle -> licence FK check FAILED!") AS '';
SELECT IF(@total_veh_fk = @total, "licence_vehicle -> vehicle FK check OK", "licence_vehicle -> vehicle FK check FAILED!") AS '';
SELECT IF((@total_app_fk + @total_null_app) = @total, "licence_vehicle -> application application_id FK check OK", "licence_vehicle -> application application_id FK check FAILED!") AS '';
SELECT IF((@total_int_app_fk + @total_null_int_app) = @total, "licence_vehicle -> application interim_application_id FK check OK", "licence_vehicle -> application interim_application_id FK check FAILED!") AS '';
 
# trailer

SELECT '-- trailer --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(l.id IS NOT NULL, 1, 0))
INTO @total, @total_lic_fk
FROM trailer t
LEFT JOIN licence l ON t.licence_id = l.id;

SELECT IF(@total_lic_fk = @total, "trailer -> licence FK check OK", "trailer -> licence FK check FAILED!") AS '';
   
# transport_manager

SELECT '-- transport_manager --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(cd1.id IS NOT NULL, 1, 0)),
    SUM(IF(cd2.id IS NOT NULL, 1, 0)),
    SUM(IF(tm.work_cd_id IS NULL, 1, 0)),
    SUM(IF(tm2.id IS NOT NULL, 1, 0)),
    SUM(IF(tm.merge_to_transport_manager_id IS NULL, 1, 0))
INTO 
    @total, @total_home_cd_fk, @total_work_cd_fk, @total_null_work_cd, 
    @total_merge_tm_fk, @total_null_merge_tm
FROM transport_manager tm
LEFT JOIN contact_details cd1 ON tm.home_cd_id = cd1.id
LEFT JOIN contact_details cd2 ON tm.work_cd_id = cd2.id
LEFT JOIN transport_manager tm2 ON tm.merge_to_transport_manager_id = tm2.id;

SELECT IF(@total_home_cd_fk = @total, "transport_manager -> contact_details home_cd_id FK check OK", "transport_manager -> contact_details home_cd_id FK check FAILED!") AS '';
SELECT IF((@total_work_cd_fk + @total_null_work_cd) = @total, "transport_manager -> contact_details work_cd_id FK check OK", "transport_manager -> contact_details work_cd_id FK check FAILED!") AS '';
SELECT IF((@total_merge_tm_fk + @total_null_merge_tm) = @total, "transport_manager -> transport_manager merge_to_transport_manager_id FK check OK", "transport_manager -> transport_manager merge_to_transport_manager_id FK check FAILED!") AS '';

# transport_manager_licence

SELECT '-- transport_manager_licence --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(l.id IS NOT NULL, 1, 0)),
    SUM(IF(tm.id IS NOT NULL, 1, 0))
INTO @total, @total_lic_fk, @total_tm_fk
FROM transport_manager_licence tml  
LEFT JOIN licence l ON tml.licence_id = l.id
LEFT JOIN transport_manager tm ON tml.transport_manager_id = tm.id; 

SELECT IF(@total_lic_fk = @total, "transport_manager_licence -> licence FK check OK", "transport_manager_licence -> licence FK check FAILED!") AS '';
SELECT IF(@total_tm_fk = @total, "transport_manager_licence -> transport_manager FK check OK", "transport_manager_licence -> transport_manager FK check FAILED!") AS '';

# community_lic

SELECT '-- community_lic --' AS '';

SELECT 
    COUNT(*),
    SUM(IF(l.id IS NOT NULL, 1, 0))
INTO @total, @total_lic_fk
FROM community_lic c 
LEFT JOIN licence l ON c.licence_id = l.id;

SELECT IF(@total_lic_fk = @total, "community_lic -> licence FK check OK", "community_lic -> licence FK check FAILED!") AS '';

END
$$
DELIMITER ;
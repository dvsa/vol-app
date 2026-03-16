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

SELECT count(*)
INTO @total
FROM licence;

SELECT count(*)
INTO @total_org_fk
FROM licence l
JOIN organisation o
ON l.organisation_id =o.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_org_fk = @total
	)
    ,"licence -> organisation FK check OK"
    ,"licence -> organisation FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_corr_fk
FROM licence l
JOIN contact_details d ON l.correspondence_cd_id = d.id;

SELECT count(*)
INTO @total_null_corr
FROM licence l
WHERE correspondence_cd_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_corr_fk + @total_null_corr) = @total
	)
    ,"licence -> contact_details correspondence_cd_id FK check OK"
    ,"licence -> contact_details correspondence_cd_id FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_tc_fk
FROM licence l
JOIN contact_details d ON l.transport_consultant_cd_id = d.id;

SELECT count(*)
INTO @total_null_tc
FROM licence l
WHERE transport_consultant_cd_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_tc_fk + @total_null_tc) = @total
	)
    ,"licence -> contact_details transport_consultant_cd_id FK check OK"
    ,"licence -> contact_details transport_consultant_cd_id FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_est_fk
FROM licence l
JOIN contact_details d ON l.establishment_cd_id = d.id;

SELECT count(*)
INTO @total_null_est
FROM licence l
WHERE establishment_cd_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_est_fk + @total_null_est) = @total
	)
    ,"licence -> contact_details establishment_cd_id FK check OK"
    ,"licence -> contact_details establishment_cd_id FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_enf_fk
FROM licence l
JOIN enforcement_area e ON l.enforcement_area_id = e.id;

SELECT count(*)
INTO @total_null_enf
FROM licence l
WHERE enforcement_area_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_enf_fk + @total_null_enf) = @total
	)
    ,"licence -> enforcement_area FK check OK"
    ,"licence -> enforcement_area FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_tra_fk
FROM licence l
JOIN traffic_area t ON l.traffic_area_id = t.id;

SELECT count(*)
INTO @total_null_tra
FROM licence l
WHERE traffic_area_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_tra_fk + @total_null_tra) = @total
	)
    ,"licence -> traffic_area FK check OK"
    ,"licence -> traffic_area FK check FAILED!"
   ) AS ''; 
   
# organisation

select '-- organisation --' AS '';

SELECT COUNT(*)
INTO @total
FROM organisation;

SELECT count(*)
INTO @total_cd_fk
FROM organisation o
JOIN contact_details d
ON o.contact_details_id = d.id;

SELECT count(*)
INTO @total_null_cd
FROM organisation
WHERE contact_details_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_cd_fk + @total_null_cd) = @total
	)
    ,"organisation -> contact_details contact_details FK check OK"
    ,"organisation -> contact_details contact_details FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_irfo_cd_fk
FROM organisation o
JOIN contact_details d
ON o.irfo_contact_details_id = d.id;

SELECT count(*)
INTO @total_null_irfo_cd
FROM organisation
WHERE irfo_contact_details_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_irfo_cd_fk + @total_null_irfo_cd) = @total
	)
    ,"organisation -> contact_details irfo_contact_details_id FK check OK"
    ,"organisation -> contact_details irfo_contact_details_id FK check FAILED!"
   ) AS ''; 

# contact_details

SELECT '-- contact_details --' AS '';

SELECT COUNT(*)
INTO @total
FROM contact_details;

SELECT count(*)
INTO @total_person_fk
FROM contact_details c
JOIN person p ON c.person_id = p.id;

SELECT count(*)
INTO @total_null_person
FROM contact_details
WHERE person_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_person_fk + @total_null_person) = @total
	)
    ,"contact_details -> person FK check OK"
    ,"contact_details -> person FK check FAILED!"
   ) AS ''; 

SELECT count(*)
INTO @total_address_fk
FROM contact_details c
JOIN address p ON c.address_id = p.id;

SELECT count(*)
INTO @total_null_address
FROM contact_details
WHERE address_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_address_fk + @total_null_address) = @total
	)
    ,"contact_details -> address FK check OK"
    ,"contact_details -> address FK check FAILED!"
   ) AS ''; 

# address - has no FKs of its own
# check tables that have address as a FK

# operating_centre

SELECT '-- operating_centre --' AS '';
 
SELECT COUNT(*)
INTO @total
FROM operating_centre;

SELECT COUNT(*)
INTO @total_address_fk
FROM operating_centre o
JOIN address a ON o.address_id = a.id;
  
SELECT COUNT(*)
INTO @total_null_address
FROM operating_centre
WHERE address_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_address_fk + @total_null_address) = @total
	)
    ,"operating_centre -> address FK check OK"
    ,"operating_centre -> address FK check FAILED!"
   ) AS ''; 

# venue

SELECT '-- venue --' AS '';

SELECT COUNT(*)
INTO @total
FROM venue;

SELECT COUNT(*)
INTO @total_address_fk
FROM venue v
JOIN address a ON v.address_id = a.id;
  
SELECT COUNT(*)
INTO @total_null_address
FROM venue
WHERE address_id is null;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_address_fk + @total_null_address) = @total
	)
    ,"venue -> address FK check OK"
    ,"venue -> address FK check FAILED!"
   ) AS ''; 

# person - has no FKs of its own
# check tables that have person as a FK

# application_organisation_person

SELECT '-- application_organisation_person --' AS '';

SELECT count(*)
INTO @total
FROM application_organisation_person;

SELECT count(*)
INTO @total_person_fk
FROM application_organisation_person a
JOIN person p ON a.person_id = p.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_person_fk = @total
	)
    ,"application_organisation_person -> person FK check OK"
    ,"application_organisation_person -> person FK check FAILED!"
   ) AS ''; 


# organisation_person

SELECT '-- organisation_person --' AS '';

SELECT count(*)
INTO @total
FROM organisation_person;

SELECT count(*)
INTO @total_person_fk
FROM organisation_person c
JOIN person p ON c.person_id = p.id;

SELECT count(*)
INTO @total_org_fk
FROM organisation_person c
JOIN organisation o ON c.organisation_id = o.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_person_fk  = @total
	)
    ,"organisation_person -> person FK check OK"
    ,"organisation_person -> person FK check FAILED!"
   ) AS ''; 

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_org_fk  = @total
	)
    ,"organisation_person -> organisation FK check OK"
    ,"organisation_person -> organisation FK check FAILED!"
   ) AS ''; 

# phone_contact

SELECT '-- phone_contact --' AS '';

SELECT count(*)
INTO @total
FROM phone_contact;

SELECT count(*)
INTO @total_cd_fk
FROM phone_contact p
JOIN contact_details c ON p.contact_details_id = c.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_cd_fk  = @total
	)
    ,"phone_contact -> contact_details FK check OK"
    ,"phone_contact -> contact_details FK check FAILED!"
   ) AS '';
 
# trading_name

SELECT '-- trading_name --' AS '';

SELECT COUNT(*)
INTO @total
FROM trading_name; 

SELECT COUNT(*)
INTO @total_lic_fk
FROM trading_name t
JOIN licence c ON t.licence_id = c.id;

SELECT count(*)
INTO @total_null_lic
FROM trading_name
WHERE licence_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_lic_fk + @total_null_lic) = @total
	)
    ,"trading_name -> licence FK check OK"
    ,"trading_name -> licence FK check FAILED!"
   ) AS '';

SELECT count(*)
INTO @total_org_fk
FROM trading_name t
JOIN organisation c ON t.organisation_id = c.id;

SELECT count(*)
INTO @total_null_org
FROM trading_name
WHERE organisation_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_org_fk + @total_null_org) = @total
	)
    ,"trading_name -> organisation FK check OK"
    ,"trading_name -> organisation FK check FAILED!"
   ) AS '';
 
# vehicle - no fks

SELECT '-- licence_vehicle --' AS '';

# licence_vehicle

SELECT count(*)
INTO @total
FROM licence_vehicle;

SELECT count(*)
INTO @total_lic_fk
FROM licence_vehicle lv
JOIN licence l ON lv.licence_id = l.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_lic_fk = @total
	)
    ,"licence_vehicle -> licence FK check OK"
    ,"licence_vehicle -> licence FK check FAILED!"
   ) AS '';

SELECT count(*)
INTO @total_veh_fk
FROM licence_vehicle lv
JOIN vehicle v ON lv.vehicle_id = v.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_veh_fk = @total
	)
    ,"licence_vehicle -> vehicle FK check OK"
    ,"licence_vehicle -> vehicle FK check FAILED!"
   ) AS '';

SELECT count(*)
INTO @total_app_fk
FROM licence_vehicle lv
JOIN application a ON lv.application_id = a.id;

SELECT count(*)
INTO @total_null_app
FROM licence_vehicle
WHERE application_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_app_fk + @total_null_app) = @total
	)
    ,"licence_vehicle -> application application_id FK check OK"
    ,"licence_vehicle -> application application_id FK check FAILED!"
   ) AS '';

SELECT count(*)
INTO @total_int_app_fk
FROM licence_vehicle lv
JOIN application a ON lv.interim_application_id = a.id;

SELECT count(*)
INTO @total_null_int_app
FROM licence_vehicle
WHERE interim_application_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_int_app_fk + @total_null_int_app) = @total
	)
    ,"licence_vehicle -> application interim_application_id FK check OK"
    ,"licence_vehicle -> application interim_application_id FK check FAILED!"
   ) AS '';
 
# trailer

SELECT '-- trailer --' AS '';

SELECT COUNT(*)
INTO @total
FROM trailer;

SELECT COUNT(*)
INTO @total_lic_fk
FROM trailer t
JOIN licence l ON t.licence_id = l.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_lic_fk = @total
	)
    ,"trailer -> licence FK check OK"
    ,"trailer -> licence FK check FAILED!"
   ) AS '';
   
# transport_manager

SELECT '-- transport_manager --' AS '';

SELECT count(*)
INTO @total
FROM transport_manager;

SELECT count(*)
INTO @total_home_cd_fk
FROM transport_manager tm
JOIN contact_details cd ON tm.home_cd_id = cd.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_home_cd_fk = @total
	)
    ,"transport_manager -> contact_details home_cd_id FK check OK"
    ,"transport_manager -> contact_details home_cd_id FK check FAILED!"
   ) AS '';


SELECT count(*)
INTO @total_work_cd_fk
FROM transport_manager tm
JOIN contact_details cd ON tm.work_cd_id = cd.id;

SELECT count(*)
INTO @total_null_work_cd
FROM transport_manager
WHERE work_cd_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_work_cd_fk + @total_null_work_cd) = @total
	)
    ,"transport_manager -> contact_details work_cd_id FK check OK"
    ,"transport_manager -> contact_details work_cd_id FK check FAILED!"
   ) AS '';

   
SELECT count(*)
INTO @total_merge_tm_fk
FROM transport_manager tm
JOIN transport_manager tm2 ON tm.merge_to_transport_manager_id = tm2.id;

SELECT count(*)
INTO @total_null_merge_tm
FROM transport_manager
WHERE merge_to_transport_manager_id IS NULL;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE (@total_merge_tm_fk + @total_null_merge_tm) = @total
	)
    ,"transport_manager -> transport_manager merge_to_transport_manager_id FK check OK"
    ,"transport_manager -> transport_manager merge_to_transport_manager_id FK check FAILED!"
   ) AS '';

# transport_manager_licence

SELECT '-- transport_manager_licence --' AS '';

SELECT count(*)
INTO @total
FROM transport_manager_licence;

SELECT count(*)
INTO @total_lic_fk
FROM transport_manager_licence tml  
JOIN licence l ON tml.licence_id = l.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_lic_fk = @total
	)
    ,"transport_manager_licence -> licence FK check OK"
    ,"transport_manager_licence -> licence FK check FAILED!"
   ) AS '';
   
SELECT count(*)
INTO @total_tm_fk
FROM transport_manager_licence tml
JOIN transport_manager tm ON tml.transport_manager_id = tm.id; 

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_tm_fk = @total
	)
    ,"transport_manager_licence -> transport_manager FK check OK"
    ,"transport_manager_licence -> transport_manager FK check FAILED!"
   ) AS '';

# community_lic

SELECT '-- community_lic --' AS '';

SELECT count(*)
INTO @total
FROM community_lic;

SELECT count(*)
INTO @total_lic_fk
FROM community_lic c 
JOIN licence l ON c.licence_id = l.id;

SELECT IF (
    exists
    (
     SELECT 1
     FROM dual
      WHERE @total_lic_fk = @total
	)
    ,"community_lic -> licence FK check OK"
    ,"community_lic -> licence FK check FAILED!"
   ) AS '';

END
$$

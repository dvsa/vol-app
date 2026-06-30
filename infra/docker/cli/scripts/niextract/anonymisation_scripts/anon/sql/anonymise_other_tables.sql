################################################################
#
# Anonymise other OLCS tables.
#
################################################################

SET SESSION group_concat_max_len = 1000000;
SET @starttimestamp = CURRENT_TIMESTAMP(6);

SET @alphabet = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
SET @numbers = '1,2,3,4,5,6,7,8,9,0';
SET @numbers2 = '1,2,3,4,5,6,7,8,9';

# disable triggers
SET @DISABLE_TRIGGERS = 1;

SELECT CONCAT(now(),' start anonymisation of other tables...') AS '';

UPDATE organisation
SET company_or_llp_no = IF(company_or_llp_no IS NOT NULL,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)
              ),company_or_llp_no);

#randomise current,prefix and suffix standard VRMs
	
UPDATE complaint
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE complaint
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE complaint
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE complaint
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE appeal	
SET outline_ground =  IF(outline_ground IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='appeal' AND column_name ='outline_ground' ORDER BY RAND() LIMIT 1),outline_ground),
	comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='appeal' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE application	
SET insolvency_details = IF(insolvency_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='application' AND column_name='insolvency_details' ORDER BY RAND() LIMIT 1),insolvency_details),
	psv_small_vhl_notes = IF(psv_small_vhl_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='application' AND column_name='psv_small_vhl_notes' ORDER BY RAND() LIMIT 1),psv_small_vhl_notes),
	psv_medium_vhl_notes = IF(psv_medium_vhl_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='application' AND column_name='psv_medium_vhl_notes' ORDER BY RAND() LIMIT 1),psv_medium_vhl_notes),
	interim_reason = IF(interim_reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='application' AND column_name='interim_reason' ORDER BY RAND() LIMIT 1),interim_reason),
	request_inspection_comment = IF(request_inspection_comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='application' AND column_name='request_inspection_comment' ORDER BY RAND() LIMIT 1),request_inspection_comment);

UPDATE bus_short_notice
SET unforseen_detail =  IF(unforseen_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='unforseen_detail' ORDER BY RAND() LIMIT 1),unforseen_detail),
    timetable_detail =  IF(timetable_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='timetable_detail' ORDER BY RAND() LIMIT 1),timetable_detail),
    replacement_detail =  IF(replacement_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='replacement_detail' ORDER BY RAND() LIMIT 1),replacement_detail),
    holiday_detail =  IF(holiday_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='holiday_detail' ORDER BY RAND() LIMIT 1),holiday_detail),
    trc_detail =  IF(trc_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='trc_detail' ORDER BY RAND() LIMIT 1),trc_detail),
    police_detail =  IF(police_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='police_detail' ORDER BY RAND() LIMIT 1),police_detail),
    special_occasion_detail =  IF(special_occasion_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='special_occasion_detail' ORDER BY RAND() LIMIT 1),special_occasion_detail),
    connection_detail =  IF(connection_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='connection_detail' ORDER BY RAND() LIMIT 1),connection_detail),
    not_available_detail =  IF(not_available_detail IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='bus_short_notice' AND column_name ='not_available_detail' ORDER BY RAND() LIMIT 1),not_available_detail);

UPDATE cases	
SET ecms_no = IF(ecms_no IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='cases' AND column_name ='ecms_no' ORDER BY RAND() LIMIT 1),ecms_no),
    description =  IF(description IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='cases' AND column_name ='description' ORDER BY RAND() LIMIT 1),description),
	annual_test_history = IF(annual_test_history IS NOT NULL,"Annual test history.",annual_test_history),
	prohibition_note =  IF(prohibition_note IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='cases' AND column_name ='prohibition_note' ORDER BY RAND() LIMIT 1),prohibition_note),
	penalties_note =  IF(penalties_note IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='cases' AND column_name ='penalties_note' ORDER BY RAND() LIMIT 1),penalties_note),
	conviction_note =  IF(conviction_note IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='cases' AND column_name ='conviction_note' ORDER BY RAND() LIMIT 1),conviction_note);

UPDATE community_lic
SET serial_no = IF(serial_no IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='community_lic' AND column_name ='serial_no' ORDER BY RAND() LIMIT 1),serial_no);;

UPDATE companies_house_alert
SET company_or_llp_no = IF(company_or_llp_no IS NOT NULL,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)
              ),company_or_llp_no);

UPDATE companies_house_officer	
SET date_of_birth = IF(date_of_birth IS NOT NULL,concat_ws('-', CEIL(RAND() * 59 + 1941), CEIL(RAND() * 12), CEIL(RAND() * 28)),date_of_birth);

UPDATE condition_undertaking 
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='condition_undertaking' AND column_name='notes' ORDER BY RAND() LIMIT 1),notes);

UPDATE contact_details	
SET fao = IF(fao IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='contact_details' AND column_name ='fao' ORDER BY RAND() LIMIT 1),fao),
	description =  IF(description IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='contact_details' AND column_name ='description' ORDER BY RAND() LIMIT 1),description);

UPDATE continuation_detail
SET other_finances_details = IF(other_finances_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='continuation_detail' AND column_name ='other_finances_details' ORDER BY RAND() LIMIT 1),other_finances_details);

UPDATE decision 
SET description =  IF(description IS NOT NULL,(select text from anonymisation_text where table_name='decision' and column_name='description' ORDER BY RAND() limit 1),description);

UPDATE disqualification 
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='disqualification' AND column_name='notes' ORDER BY RAND() LIMIT 1),notes);

UPDATE document
SET description =  IF(description IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='document' AND column_name ='description' ORDER BY RAND() LIMIT 1),description);

#randomise current,prefix and suffix standard VRMs
	
UPDATE erru_request
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE erru_request
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);


UPDATE erru_request
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE erru_request
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE grace_period
SET description =  IF(description IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='grace_period' AND column_name ='description' ORDER BY RAND() LIMIT 1),description);
    
UPDATE impounding
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='impounding' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes);

#randomise current,prefix and suffix standard VRMs
	
UPDATE impounding
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE impounding
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);


UPDATE impounding
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE impounding
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);
	
UPDATE inspection_email
SET subject =  IF(subject IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='inspection_email' AND column_name ='subject' ORDER BY RAND() LIMIT 1),subject),
    message_body =  IF(message_body IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='inspection_email' AND column_name ='message_body' ORDER BY RAND() LIMIT 1),message_body);
    
UPDATE inspection_request
SET requestor_notes = IF(requestor_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='inspection_request' AND column_name ='requestor_notes' ORDER BY RAND() LIMIT 1),requestor_notes),
    inspector_notes = IF(inspector_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='inspection_request' AND column_name ='inspector_notes' ORDER BY RAND() LIMIT 1),inspector_notes);

UPDATE irfo_gv_permit
SET note =  IF(note IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='irfo_gv_permit' AND column_name ='note' ORDER BY RAND() LIMIT 1),note);

UPDATE irfo_partner
SET name =  IF(name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='irfo_partner' AND column_name ='name' ORDER BY RAND() LIMIT 1),name);

UPDATE irfo_psv_auth	
SET exemption_details =  IF(exemption_details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='irfo_psv_auth' AND column_name ='exemption_details' ORDER BY RAND() LIMIT 1),exemption_details),
	service_route_from =  IF(service_route_from IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='irfo_psv_auth' AND column_name ='service_route_from' ORDER BY RAND() LIMIT 1),service_route_from),
	service_route_to =  IF(service_route_to IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='irfo_psv_auth' AND column_name ='service_route_to' ORDER BY RAND() LIMIT 1),service_route_to);

#randomise current,prefix and suffix standard VRMs
	
UPDATE irfo_vehicle
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE irfo_vehicle
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE irfo_vehicle
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE irfo_vehicle
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE legacy_offence
SET notes = IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='legacy_offence' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes),
    offence_authority = IF(offence_authority IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='legacy_offence' AND column_name ='offence_authority' ORDER BY RAND() LIMIT 1),offence_authority),
    offender_name = IF(offender_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='legacy_offence' AND column_name ='offender_name' ORDER BY RAND() LIMIT 1),offender_name);

#randomise current,prefix and suffix standard VRMs
	
UPDATE legacy_offence
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE legacy_offence
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE legacy_offence
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE legacy_offence
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);
	
UPDATE legacy_recommendation
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='legacy_recommendation' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment),
    notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='legacy_recommendation' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes);
	
UPDATE licence
SET tachograph_ins_name =  IF(tachograph_ins_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='licence' AND column_name ='tachograph_ins_name' ORDER BY RAND() LIMIT 1),tachograph_ins_name);;

UPDATE note 
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='note' AND column_name='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE opposition
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='opposition' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes),
    valid_notes =  IF(valid_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='opposition' AND column_name ='valid_notes' ORDER BY RAND() LIMIT 1),valid_notes);

UPDATE other_licence
SET holder_name = IF(holder_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='other_licence' AND column_name ='holder_name' ORDER BY RAND() LIMIT 1),holder_name),
    disqualification_length = IF(disqualification_length IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='other_licence' AND column_name ='disqualification_length' ORDER BY RAND() LIMIT 1),disqualification_length),
    additional_information = IF(additional_information IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='other_licence' AND column_name ='additional_information' ORDER BY RAND() LIMIT 1),additional_information), 
    operating_centres = IF(operating_centres IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='other_licence' AND column_name ='operating_centres' ORDER BY RAND() LIMIT 1),operating_centres);

# update phone_contact with varying number formats

UPDATE phone_contact
set phone_number = NULL,
	details = IF(details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='phone_contact' AND column_name ='details' ORDER BY RAND() LIMIT 1),details);

# 0nnn nnn nnnn

UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 80000;

# 0nnnn nnn nnn

UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 4000;


# 0nnn - nnn - nnnn
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' - ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' - ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#0nnn nnn nnnn nnnn
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 10000;

# NOT GIVEN
UPDATE phone_contact
set phone_number = 'NOT GIVEN'
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 10000;

# N/A
UPDATE phone_contact
set phone_number = 'N/A'
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 10000;

#NA
UPDATE phone_contact
set phone_number = 'NA'
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 10000;

# 0nnn nnn nnnn, EXT nnnn
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ', EXT ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#0nnnnnnnnnn, 0nnnnnnnnnn(FAX)
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ', 0',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1), 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              '(FAX)') 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#0nnnn nnnnnn,
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1), 
              ',') 
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 10000;

UPDATE phone_contact
set phone_number = 'JOHN DOE'
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#+44nnnnnnnnn
UPDATE phone_contact
set phone_number = CONCAT('+44',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;
 
#+44nnnnnnnnnn
UPDATE phone_contact
set phone_number = CONCAT('+44',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#+44 nnnnnnnnnn
UPDATE phone_contact
set phone_number = CONCAT('+44 ',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 50000;

#+44 (0)nn nnnn nnnn
UPDATE phone_contact
set phone_number = CONCAT('+44 (0)',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 1000;

#+44nnnnnnnnnnFAX+44nnnnnnnnn
UPDATE phone_contact
set phone_number = CONCAT('+44',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              'FAX+44',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 2000;

#+34 nnn nnn nnn
UPDATE phone_contact
set phone_number = CONCAT('+34 ',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 2000;

#0nnn nnnnnnn/nn
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              '/',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1))
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 1000;

#0nnnnnnnnnn - X X SMITH
UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' - ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              ' SMITH')
WHERE phone_number IS NULL               
ORDER BY RAND()
LIMIT 2000;

#0nnnn nnnnnn Freds Mobile

UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' ',
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              ' Fred\'s Mobile')
WHERE phone_number IS NULL             
ORDER BY RAND()
LIMIT 2000;

# all other numbers format as 0nnnnnnnnnn

UPDATE phone_contact
set phone_number = CONCAT('0',
 SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers2, ',', CEIL(RAND() * 9)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1)) 
WHERE phone_number IS NULL;            

UPDATE pi
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment),
    decision_notes =  IF(decision_notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi' AND column_name ='decision_notes' ORDER BY RAND() LIMIT 1),decision_notes);
    
UPDATE pi_hearing
SET cancelled_reason = IF(cancelled_reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi_hearing' AND column_name ='cancelled_reason' ORDER BY RAND() LIMIT 1),cancelled_reason),
    adjourned_reason = IF(adjourned_reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi_hearing' AND column_name ='adjourned_reason' ORDER BY RAND() LIMIT 1),adjourned_reason),
    details = IF(details IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi_hearing' AND column_name ='details' ORDER BY RAND() LIMIT 1),details);
    
UPDATE prohibition
SET imposed_at = IF(imposed_at IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='pi_hearing' AND column_name ='imposed_at' ORDER BY RAND() LIMIT 1),imposed_at);

#randomise current,prefix and suffix standard VRMs
	
UPDATE prohibition
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE prohibition
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE prohibition
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE prohibition
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE prohibition_defect
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='prohibition_defect' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes);

UPDATE propose_to_revoke
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='propose_to_revoke' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE recipient
SET contact_name = IF(contact_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='recipient' AND column_name ='contact_name' ORDER BY RAND() LIMIT 1),contact_name);
 
UPDATE serious_infringement
SET reason = IF(reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='serious_infringement' AND column_name ='reason' ORDER BY RAND() LIMIT 1),reason);

UPDATE statement
SET authorisers_decision = IF(authorisers_decision IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='statement' AND column_name ='authorisers_decision' ORDER BY RAND() LIMIT 1),authorisers_decision);

#randomise current,prefix and suffix standard VRMs
	
UPDATE statement
SET vrm = IF(vrm REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 

UPDATE statement
SET vrm = IF(vrm REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$',
            CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE statement
SET vrm = IF(vrm REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$' ,
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm); 
	
# any VRMs not current,prefix or suffix standard set to random suffix

UPDATE statement
SET vrm = IF(vrm IS NOT NULL AND vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$',
           CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ),vrm);

UPDATE stay
SET notes =  IF(notes IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='stay' AND column_name ='notes' ORDER BY RAND() LIMIT 1),notes);

UPDATE submission
SET data_snapshot = IF(data_snapshot IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='submission' AND column_name ='data_snapshot' ORDER BY RAND() LIMIT 1),data_snapshot);

UPDATE submission_action
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='submission_action' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE submission_section_comment 
SET comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='submission_section_comment' AND column_name='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE tm_case_decision	
SET repute_not_lost_reason = IF(repute_not_lost_reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='tm_case_decision' AND column_name ='repute_not_lost_reason' ORDER BY RAND() LIMIT 1),repute_not_lost_reason),
    no_further_action_reason = IF(no_further_action_reason IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='tm_case_decision' AND column_name ='no_further_action_reason' ORDER BY RAND() LIMIT 1),no_further_action_reason);

UPDATE tm_employment
SET position = IF(position IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='tm_employment' AND column_name ='position' ORDER BY RAND() LIMIT 1),position),
    employer_name = IF(employer_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='tm_employment' AND column_name ='employer_name' ORDER BY RAND() LIMIT 1),employer_name);

UPDATE trailer
SET trailer_no = CONCAT(
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1));

UPDATE transport_manager_application
SET additional_information = IF(additional_information IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='transport_manager_application' AND column_name ='additional_information' ORDER BY RAND() LIMIT 1),additional_information);

UPDATE transport_manager_licence
SET additional_information = IF(additional_information IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='transport_manager_licence' AND column_name ='additional_information' ORDER BY RAND() LIMIT 1),additional_information);

UPDATE txn
SET payer_name = IF(payer_name IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='txn' AND column_name ='payer_name' ORDER BY RAND() LIMIT 1),payer_name),
	comment =  IF(comment IS NOT NULL,(SELECT text FROM anonymisation_text WHERE table_name='txn' AND column_name ='comment' ORDER BY RAND() LIMIT 1),comment);

UPDATE user
SET login_id = IF(login_id IS NOT NULL,CONCAT('usr', id),login_id),
	pid = IF(pid IS NOT NULL,SHA2(CONCAT('usr', id), 256),pid);

#randomise current,prefix and suffix standard VRMs	

UPDATE vehicle
SET vrm = CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ) 
WHERE vrm  REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$';

UPDATE vehicle
SET vrm = CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ) 
WHERE vrm  REGEXP '^[A-Z][0-9]{1,3}[A-Z]{3}$';

UPDATE vehicle
SET vrm = CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ) 
WHERE vrm  REGEXP '^[A-Z]{3}[0-9]{1,3}[A-Z]$';
	
# any VRMs not current,prefix or suffix standard set to random suffix
  
UPDATE vehicle
SET vrm = CONCAT( 
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@numbers, ',', CEIL(RAND() * 10)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1),
              SUBSTRING_INDEX(SUBSTRING_INDEX(@alphabet, ',', CEIL(RAND() * 26)),',',-1)
              ) 
WHERE vrm NOT REGEXP '^[A-Z]{2}[0-9]{2}[A-Z]{3}$|^[A-Z][0-9]{1,3}[A-Z]{3}$|^[A-Z]{3}[0-9]{1,3}[A-Z]$';
 
# enable triggers
SET @DISABLE_TRIGGERS = NULL;

SELECT CONCAT(now(),' anonymisation of other tables complete.') AS '';


################################################################
#
#   Anonymise OLCS _hist tables.
#
################################################################

SELECT CONCAT(now(),' anonymise history tables...') as '';

UPDATE address_hist a
JOIN address b ON a.id = b.id
SET a.paon_desc = b.paon_desc,
    a.saon_desc = b.saon_desc,
    a.street = b.street,
    a.locality = b.locality,
    a.town = b.town,
    a.postcode = b.postcode,
    a.hist_db_user = "anon";

UPDATE answer_hist a
JOIN answer b ON a.id = b.id
SET a.ans_text = b.ans_text,
    a.hist_db_user = "anon";

UPDATE companies_house_company_hist a
JOIN companies_house_company b ON a.id = b.id
SET a.company_number = b.company_number,
    a.company_name = b.company_name,
    a.company_status= b.company_status,
    a.address_line_1= b.address_line_1,
    a.address_line_2= b.address_line_2,
    a.country= b.country,
    a.po_box= b.po_box,
    a.postal_code= b.postal_code,
    a.region = b.region,
    a.premises = b.premises,
    a.hist_db_user = "anon";

UPDATE person_hist a
JOIN person b on a.id = b.id
SET a.forename = b.forename,
    a.family_name = b.family_name,
    a.birth_date = b.birth_date,
    a.birth_place = b.birth_place,
    a.other_name = b.other_name,
    a.hist_db_user = "anon";
	
UPDATE publication_police_data_hist a
JOIN publication_police_data b ON a.id = b.id	
SET a.forename = b.forename,
    a.family_name= b.family_name,
    a.birth_date = b.birth_date,
    a.olbs_dob = b.olbs_dob,
    a.hist_db_user = "anon";

UPDATE organisation_hist a
JOIN organisation b ON a.id = b.id
SET a.company_or_llp_no= b.company_or_llp_no,
    a.name= b.name,
    a.hist_db_user = "anon";

UPDATE complaint_hist a
JOIN complaint b ON a.id = b.id
SET a.driver_forename = b.driver_forename,
    a.driver_family_name = b.driver_family_name,
    a.description = b.description,
    a.hist_db_user = "anon";

UPDATE conviction_hist a
JOIN conviction b ON a.id = b.id
SET a.person_firstname = b.person_firstname,
    a.person_lastname = b.person_lastname,
    a.penalty = b.penalty,
    a.costs = a.costs,
    a.birth_date = b.birth_date,
    a.notes = b.notes,
    a.taken_into_consideration = b.taken_into_consideration,
    a.operator_name = b.operator_name,
    a.hist_db_user = "anon";
	
UPDATE previous_conviction_hist a
JOIN previous_conviction b ON a.id = b.id
SET a.forename = b.forename, 
    a.family_name = b.family_name,
    a.birth_date = b.birth_date,
    a.category_text = b.category_text,
    a.notes = b.notes,
    a.court_fpn = b.court_fpn,
    a.penalty = b.penalty,
    a.hist_db_user = "anon";

UPDATE transport_manager_hist a
JOIN transport_manager b ON a.id = b.id
SET a.nysiis_forename=b.nysiis_forename,
    a.nysiis_family_name=b.nysiis_family_name,
    a.hist_db_user = "anon";
	
UPDATE trading_name_hist a
JOIN trading_name b ON a.id = b.id
SET a.name = b.name,
    a.hist_db_user = "anon";

UPDATE bus_reg_hist a
JOIN bus_reg b ON a.id = b.id
SET a.start_point = b.start_point,
    a.finish_point = b.finish_point,
    a.via = b.via,
    a.other_details = b.other_details,
    a.manoeuvre_detail = b.manoeuvre_detail,
    a.new_stop_detail = b.new_stop_detail,
    a.not_fixed_stop_detail = b.not_fixed_stop_detail,
    a.subsidy_detail = b.subsidy_detail,
    a.route_description = b.route_description,
    a.stopping_arrangements = b.stopping_arrangements,
    a.trc_notes = b.trc_notes,
    a.organisation_email = b.organisation_email,
    a.reason_cancelled = b.reason_cancelled,
    a.reason_refused = b.reason_refused,
    a.reason_sn_refused = b.reason_sn_refused,
    a.quality_partnership_details = b.quality_partnership_details,
    a.quality_contract_details = b.quality_contract_details,
    a.hist_db_user = "anon";

UPDATE appeal_hist a
JOIN appeal b ON a.id = b.id
SET a.outline_ground = b.outline_ground,
    a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE application_hist a
JOIN application b ON a.id = b.id
SET a.insolvency_details = b.insolvency_details,
    a.psv_small_vhl_notes = b.psv_small_vhl_notes,
    a.psv_medium_vhl_notes = b.psv_medium_vhl_notes,
    a.interim_reason = b.interim_reason,
    a.request_inspection_comment = b.request_inspection_comment,
    a.hist_db_user = "anon";

UPDATE bus_short_notice_hist a
JOIN bus_short_notice b ON a.id = b.id
SET a.unforseen_detail = b.unforseen_detail,
    a.timetable_detail = b.timetable_detail,
    a.replacement_detail = b.replacement_detail,
    a.holiday_detail = b.holiday_detail,
    a.trc_detail = b.trc_detail,
    a.police_detail = b.police_detail,
    a.special_occasion_detail = b.special_occasion_detail,
    a.connection_detail = b.connection_detail,
    a.not_available_detail = b.not_available_detail,
    a.hist_db_user = "anon";

UPDATE cases_hist a
JOIN cases b ON a.id = b.id
SET a.ecms_no = b.ecms_no,
    a.description = b.description,
    a.annual_test_history = b.annual_test_history,
    a.prohibition_note = b.prohibition_note,
    a.penalties_note = b.penalties_note,
    a.conviction_note = b.conviction_note,
    a.hist_db_user = "anon";
	
UPDATE change_of_entity_hist a
JOIN change_of_entity b ON a.id = b.id
SET a.old_organisation_name = b.old_organisation_name,
    a.hist_db_user = "anon";

UPDATE community_lic_hist a
JOIN community_lic b ON a.id = b.id 
SET a.serial_no = b.serial_no,
    a.hist_db_user = "anon";

UPDATE companies_house_alert_hist a
JOIN companies_house_alert b ON a.id = b.id
SET a.company_or_llp_no = b.company_or_llp_no,
    a.hist_db_user = "anon";

UPDATE companies_house_officer_hist a
JOIN companies_house_officer b ON a.id = b.id
SET a.name = b.name,
    a.date_of_birth = b.date_of_birth,
    a.hist_db_user = "anon";

UPDATE company_subsidiary_hist a
JOIN company_subsidiary b ON a.id = b.id	
SET a.name = b.name,
    a.company_no = b.company_no,
    a.hist_db_user = "anon";

UPDATE condition_undertaking_hist a	
JOIN condition_undertaking b ON a.id = b.id
SET a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE contact_details_hist a
JOIN contact_details b ON a.id = b.id
SET a.email_address = b.email_address,
    a.fao = b.fao,
    a.description = b.description,
    a.hist_db_user = "anon";

UPDATE decision_hist a
JOIN decision b ON a.id = b.id
SET a.description = b.description,
    a.hist_db_user = "anon";

UPDATE disqualification_hist a
JOIN disqualification b ON a.id = b.id 
SET a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE document_hist a
JOIN document b ON a.id = b.id
SET a.description = b.description,
    a.hist_db_user = "anon";

UPDATE ebsr_submission_hist a
JOIN ebsr_submission b ON a.id = b.id
SET a.organisation_email_address = b.organisation_email_address,
    a.hist_db_user = "anon";

UPDATE enforcement_area_hist a
JOIN enforcement_area b ON a.id = b.id
SET a.email_address = b.email_address,
    a.hist_db_user = "anon";

UPDATE erru_request_hist a
JOIN erru_request b ON a.id = b.id
SET a.vrm = b.vrm,
    a.hist_db_user = "anon";

UPDATE grace_period_hist a
JOIN grace_period b ON a.id = b.id
SET a.description = b.description,
    a.hist_db_user = "anon";
    
UPDATE impounding_hist a
JOIN impounding b ON a.id = b.id
SET a.vrm = b.vrm,
    a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE inspection_email_hist a	
JOIN inspection_email b ON a.id = b.id
SET a.subject = b.subject,
    a.message_body = b.message_body,
    a.sender_email_address = b.sender_email_address,
    a.hist_db_user = "anon";

UPDATE inspection_request_hist a
JOIN inspection_request b ON a.id = b.id
SET a.requestor_notes = b.requestor_notes,
    a.inspector_notes = b.inspector_notes,
    a.hist_db_user = "anon";
    
UPDATE irfo_gv_permit_hist a
JOIN irfo_gv_permit b ON a.id = b.id
SET a.note = b.note,
    a.hist_db_user = "anon";

UPDATE irfo_partner_hist a
JOIN irfo_partner b ON a.id = b.id
SET a.name = b.name,
    a.hist_db_user = "anon";

UPDATE irfo_psv_auth_hist a
JOIN irfo_psv_auth b ON a.id = b.id	
SET a.exemption_details = b.exemption_details,
    a.service_route_from = b.service_route_from,
    a.service_route_to = b.service_route_to,
    a.hist_db_user = "anon";

UPDATE irfo_vehicle_hist a
JOIN irfo_vehicle b ON a.id = b.id
SET a.vrm = b.vrm,
    a.hist_db_user = "anon";

UPDATE legacy_offence_hist a
JOIN legacy_offence b ON a.id = b.id
SET a.notes = b.notes,
    a.offence_authority = b.offence_authority,
    a.offender_name = b.offender_name,
    a.vrm = b.vrm,
    a.hist_db_user = "anon";

UPDATE legacy_recommendation_hist a
JOIN legacy_recommendation b ON a.id = b.id
SET a.comment = b.comment,
    a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE licence_hist a
JOIN licence b ON a.id = b.id
SET a.tachograph_ins_name = b.tachograph_ins_name,
    a.hist_db_user = "anon";

UPDATE local_authority_hist a
JOIN local_authority b ON a.id = b.id
SET a.email_address = b.email_address,
    a.hist_db_user = "anon";

UPDATE note_hist a
JOIN note b ON a.id = b.id
SET a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE opposition_hist a
JOIN opposition b ON a.id = b.id
SET a.notes = b.notes,
    a.valid_notes = b.valid_notes,
    a.hist_db_user = "anon";

UPDATE other_licence_hist a
JOIN other_licence b ON a.id = b.id	
SET a.holder_name = b.holder_name,
    a.disqualification_length = b.disqualification_length,
    a.additional_information = b.additional_information,
    a.operating_centres = b.operating_centres,
    a.hist_db_user = "anon";

UPDATE phone_contact_hist a
JOIN phone_contact b ON a.id = b.id
SET a.phone_number = b.phone_number,
    a.details = b.details,
    a.hist_db_user = "anon";

UPDATE pi_hist a
JOIN pi b ON a.id = b.id
SET a.decision_notes = b.decision_notes,
    a.comment = b.comment,
    a.hist_db_user = "anon";
    
UPDATE pi_hearing_hist a
JOIN pi_hearing b ON a.id = b.id
SET a.cancelled_reason = b.cancelled_reason,
    a.adjourned_reason = b.adjourned_reason,
    a.details = b.details,
    a.hist_db_user = "anon";

UPDATE prohibition_hist a
JOIN prohibition b ON a.id = b.id
SET a.vrm = b.vrm,
    a.imposed_at = b.imposed_at,
    a.hist_db_user = "anon";

UPDATE prohibition_defect_hist a
JOIN prohibition_defect b ON a.id = b.id
SET a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE propose_to_revoke_hist a
JOIN propose_to_revoke b ON a.id = b.id
SET a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE recipient_hist a
JOIN recipient b ON a.id = b.id
SET a.contact_name = b.contact_name,
    a.email_address = b.email_address,
    a.hist_db_user = "anon";

UPDATE serious_infringement_hist a
JOIN serious_infringement b ON a.id = b.id
SET a.reason=b.reason,
    a.hist_db_user = "anon";

UPDATE statement_hist a
JOIN statement b ON a.id = b.id
SET a.vrm = b.vrm,
    a.authorisers_decision = b.authorisers_decision,
    a.hist_db_user = "anon";

UPDATE stay_hist a
JOIN stay b ON a.id = b.id
SET a.notes = b.notes,
    a.hist_db_user = "anon";

UPDATE submission_hist a
JOIN submission b ON a.id = b.id
SET a.data_snapshot = b.data_snapshot,
    a.hist_db_user = "anon"; 

UPDATE submission_action_hist a
JOIN submission_action b ON a.id = b.id
SET a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE submission_section_comment_hist a
JOIN submission_section_comment b ON a.id = b.id
SET a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE tm_case_decision_hist a
JOIN tm_case_decision b ON a.id = b.id
SET a.repute_not_lost_reason = b.repute_not_lost_reason,
    a.no_further_action_reason = b.no_further_action_reason,
    a.hist_db_user = "anon";

UPDATE tm_employment_hist a
JOIN tm_employment b ON a.id = b.id
SET a.position = b.position,
    a.employer_name = b.employer_name,
    a.hist_db_user = "anon";

UPDATE trailer_hist a
JOIN trailer b ON a.id = b.id
SET a.trailer_no = b.trailer_no,
    a.hist_db_user = "anon";

UPDATE transport_manager_application_hist a
JOIN transport_manager_application b ON a.id = b.id
SET a.additional_information = b.additional_information,
    a.hist_db_user = "anon";

UPDATE transport_manager_licence_hist a
JOIN transport_manager_licence b ON a.id = b.id
SET a.additional_information = b.additional_information,
    a.hist_db_user = "anon";

UPDATE txn_hist a
JOIN txn b ON a.id = b.id
SET a.payer_name = b.payer_name,
    a.comment = b.comment,
    a.hist_db_user = "anon";

UPDATE user_hist a
JOIN user b ON a.id = b.id
SET a.login_id = b.login_id,
    a.hist_db_user = "anon";

UPDATE vehicle_hist a
JOIN vehicle b ON a.id = b.id
SET a.vrm = b.vrm,
    a.hist_db_user = "anon";

UPDATE messaging_content_hist a
JOIN messaging_content b ON a.id = b.id
SET a.text = b.text;

#anonymise hist_db_user in other hist tables
SELECT CONCAT(now(),' ...anonymise hist_db_user in other hist tables...') as '';

UPDATE admin_area_traffic_area_hist SET hist_db_user = "anon";
UPDATE application_completion_hist SET hist_db_user = "anon";
UPDATE application_operating_centre_hist SET hist_db_user = "anon";
UPDATE application_organisation_person_hist SET hist_db_user = "anon";
UPDATE application_path_group_hist SET hist_db_user = "anon";
UPDATE application_path_hist SET hist_db_user = "anon";
UPDATE application_step_hist SET hist_db_user = "anon";
UPDATE application_tracking_hist SET hist_db_user = "anon";
UPDATE application_validation_hist SET hist_db_user = "anon";
UPDATE bus_notice_period_hist SET hist_db_user = "anon";
UPDATE bus_reg_bus_service_type_hist SET hist_db_user = "anon";
UPDATE bus_reg_local_auth_hist SET hist_db_user = "anon";
UPDATE bus_reg_other_service_hist SET hist_db_user = "anon";
UPDATE bus_reg_traffic_area_hist SET hist_db_user = "anon";
UPDATE bus_reg_variation_reason_hist SET hist_db_user = "anon";
UPDATE bus_service_type_hist SET hist_db_user = "anon";
UPDATE case_category_hist SET hist_db_user = "anon";
UPDATE case_outcome_hist SET hist_db_user = "anon";
UPDATE category_hist SET hist_db_user = "anon";
UPDATE community_lic_suspension_hist SET hist_db_user = "anon";
UPDATE community_lic_suspension_reason_hist SET hist_db_user = "anon";
UPDATE community_lic_withdrawal_hist SET hist_db_user = "anon";
UPDATE community_lic_withdrawal_reason_hist SET hist_db_user = "anon";
UPDATE companies_house_alert_reason_hist SET hist_db_user = "anon";
UPDATE companies_house_insolvency_practitioner_hist SET hist_db_user = "anon";
UPDATE continuation_detail_hist SET hist_db_user = "anon";
UPDATE continuation_hist SET hist_db_user = "anon";
UPDATE correspondence_inbox_hist SET hist_db_user = "anon";
UPDATE country_hist SET hist_db_user = "anon";
UPDATE data_retention_hist SET hist_db_user = "anon";
UPDATE data_retention_rule_hist SET hist_db_user = "anon";
UPDATE digital_signature_hist SET hist_db_user = "anon";
UPDATE disc_sequence_hist SET hist_db_user = "anon";
UPDATE doc_bookmark_hist SET hist_db_user = "anon";
UPDATE doc_paragraph_bookmark_hist SET hist_db_user = "anon";
UPDATE doc_paragraph_hist SET hist_db_user = "anon";
UPDATE doc_template_bookmark_hist SET hist_db_user = "anon";
UPDATE doc_template_hist SET hist_db_user = "anon";
UPDATE ebsr_route_reprint_hist SET hist_db_user = "anon";
UPDATE erru_request_failure_hist SET hist_db_user = "anon";
UPDATE event_history_type_hist SET hist_db_user = "anon";
UPDATE feature_toggle_hist SET hist_db_user = "anon";
UPDATE fee_hist SET hist_db_user = "anon";
UPDATE fee_txn_hist SET hist_db_user = "anon";
UPDATE fee_type_hist SET hist_db_user = "anon";
UPDATE financial_standing_rate_hist SET hist_db_user = "anon";
UPDATE goods_disc_hist SET hist_db_user = "anon";
UPDATE hearing_hist SET hist_db_user = "anon";
UPDATE impounding_legislation_type_hist SET hist_db_user = "anon";
UPDATE irfo_country_hist SET hist_db_user = "anon";
UPDATE irfo_gv_permit_type_hist SET hist_db_user = "anon";
UPDATE irfo_permit_stock_hist SET hist_db_user = "anon";
UPDATE irfo_psv_auth_country_hist SET hist_db_user = "anon";
UPDATE irfo_psv_auth_number_hist SET hist_db_user = "anon";
UPDATE irfo_psv_auth_type_hist SET hist_db_user = "anon";
UPDATE irhp_application_country_link_hist SET hist_db_user = "anon";
UPDATE irhp_application_hist SET hist_db_user = "anon";
UPDATE irhp_candidate_permit_hist SET hist_db_user = "anon";
UPDATE irhp_permit_application_hist SET hist_db_user = "anon";
UPDATE irhp_permit_hist SET hist_db_user = "anon";
UPDATE irhp_permit_jurisdiction_quota_hist SET hist_db_user = "anon";
UPDATE irhp_permit_range_attribute_hist SET hist_db_user = "anon";
UPDATE irhp_permit_range_country_hist SET hist_db_user = "anon";
UPDATE irhp_permit_range_hist SET hist_db_user = "anon";
UPDATE irhp_permit_request_attribute_hist SET hist_db_user = "anon";
UPDATE irhp_permit_request_hist SET hist_db_user = "anon";
UPDATE irhp_permit_sector_quota_hist SET hist_db_user = "anon";
UPDATE irhp_permit_stock_hist SET hist_db_user = "anon";
UPDATE irhp_permit_type_hist SET hist_db_user = "anon";
UPDATE irhp_permit_window_hist SET hist_db_user = "anon";
UPDATE language_hist SET hist_db_user = "anon";
UPDATE legacy_case_action_hist SET hist_db_user = "anon";
UPDATE legacy_pi_reason_hist SET hist_db_user = "anon";
UPDATE legacy_recommendation_pi_reason_hist SET hist_db_user = "anon";
UPDATE licence_no_gen_hist SET hist_db_user = "anon";
UPDATE licence_operating_centre_hist SET hist_db_user = "anon";
UPDATE licence_status_decision_hist SET hist_db_user = "anon";
UPDATE licence_status_rule_hist SET hist_db_user = "anon";
UPDATE licence_vehicle_hist SET hist_db_user = "anon";
UPDATE message_failures_hist SET hist_db_user = "anon";
UPDATE oc_complaint_hist SET hist_db_user = "anon";
UPDATE operating_centre_hist SET hist_db_user = "anon";
UPDATE operating_centre_opposition_hist SET hist_db_user = "anon";
UPDATE opposer_hist SET hist_db_user = "anon";
UPDATE opposition_grounds_hist SET hist_db_user = "anon";
UPDATE organisation_person_hist SET hist_db_user = "anon";
UPDATE organisation_type_hist SET hist_db_user = "anon";
UPDATE organisation_user_hist SET hist_db_user = "anon";
UPDATE permission_hist SET hist_db_user = "anon";
UPDATE pi_decision_hist SET hist_db_user = "anon";
UPDATE pi_definition_hist SET hist_db_user = "anon";
UPDATE pi_reason_hist SET hist_db_user = "anon";
UPDATE pi_tm_decision_hist SET hist_db_user = "anon";
UPDATE pi_type_hist SET hist_db_user = "anon";
UPDATE postcode_enforcement_area_hist SET hist_db_user = "anon";
UPDATE presiding_tc_hist SET hist_db_user = "anon";
UPDATE printer_hist SET hist_db_user = "anon";
UPDATE private_hire_licence_hist SET hist_db_user = "anon";
UPDATE psv_disc_hist SET hist_db_user = "anon";
UPDATE ptr_reason_hist SET hist_db_user = "anon";
UPDATE publication_hist SET hist_db_user = "anon";
UPDATE publication_link_hist SET hist_db_user = "anon";
UPDATE publication_section_hist SET hist_db_user = "anon";
UPDATE public_holiday_hist SET hist_db_user = "anon";
UPDATE question_hist SET hist_db_user = "anon";
UPDATE question_text_hist SET hist_db_user = "anon";
UPDATE queue_hist SET hist_db_user = "anon";
UPDATE reason_hist SET hist_db_user = "anon";
UPDATE recipient_traffic_area_hist SET hist_db_user = "anon";
UPDATE ref_data_hist SET hist_db_user = "anon";
UPDATE replacement_hist SET hist_db_user = "anon";
UPDATE role_hist SET hist_db_user = "anon";
UPDATE role_permission_hist SET hist_db_user = "anon";
UPDATE s4_hist SET hist_db_user = "anon";
UPDATE scan_hist SET hist_db_user = "anon";
UPDATE sectors_hist SET hist_db_user = "anon";
UPDATE si_category_hist SET hist_db_user = "anon";
UPDATE si_category_type_hist SET hist_db_user = "anon";
UPDATE si_penalty_erru_imposed_hist SET hist_db_user = "anon";
UPDATE si_penalty_erru_requested_hist SET hist_db_user = "anon";
UPDATE si_penalty_hist SET hist_db_user = "anon";
UPDATE si_penalty_imposed_type_hist SET hist_db_user = "anon";
UPDATE si_penalty_requested_type_hist SET hist_db_user = "anon";
UPDATE si_penalty_type_hist SET hist_db_user = "anon";
UPDATE sla_hist SET hist_db_user = "anon";
UPDATE sla_target_date_hist SET hist_db_user = "anon";
UPDATE submission_action_reason_hist SET hist_db_user = "anon";
UPDATE submission_action_type_hist SET hist_db_user = "anon";
UPDATE sub_category_description_hist SET hist_db_user = "anon";
UPDATE sub_category_hist SET hist_db_user = "anon";
UPDATE surrender_hist SET hist_db_user = "anon";
UPDATE system_info_message_hist SET hist_db_user = "anon";
UPDATE system_parameter_hist SET hist_db_user = "anon";
UPDATE tag_hist SET hist_db_user = "anon";
UPDATE task_allocation_rule_hist SET hist_db_user = "anon";
UPDATE task_alpha_split_hist SET hist_db_user = "anon";
UPDATE task_hist SET hist_db_user = "anon";
UPDATE team_hist SET hist_db_user = "anon";
UPDATE team_printer_hist SET hist_db_user = "anon";
UPDATE template_hist SET hist_db_user = "anon";
UPDATE template_test_data_hist SET hist_db_user = "anon";
UPDATE tm_case_decision_rehab_hist SET hist_db_user = "anon";
UPDATE tm_case_decision_unfitness_hist SET hist_db_user = "anon";
UPDATE tm_qualification_hist SET hist_db_user = "anon";
UPDATE traffic_area_enforcement_area_hist SET hist_db_user = "anon";
UPDATE traffic_area_hist SET hist_db_user = "anon";
UPDATE translation_key_hist SET hist_db_user = "anon";
UPDATE translation_key_text_hist SET hist_db_user = "anon";
UPDATE txc_inbox_hist SET hist_db_user = "anon";
UPDATE user_role_hist SET hist_db_user = "anon";
UPDATE venue_hist SET hist_db_user = "anon";
UPDATE workshop_hist SET hist_db_user = "anon";
UPDATE messaging_content_hist SET hist_db_user = 'anon';
UPDATE messaging_user_message_read_hist SET hist_db_user = 'anon';
UPDATE messaging_conversation_hist SET hist_db_user = 'anon';
UPDATE messaging_message_hist SET hist_db_user = 'anon';
UPDATE messaging_subject_hist SET hist_db_user = 'anon';

SELECT CONCAT(now(),' ...anonymise history tables complete.') as '';

SELECT 'DROP PROCEDURE IF EXISTS sp_drop_indices;' AS '';
SELECT 'DELIMITER \$\$' AS '';
SELECT 'CREATE PROCEDURE sp_drop_indices()' AS '';
SELECT 'BEGIN' AS '';

# drop indices on olbs_key and olbs_type only

    
SELECT CONCAT('IF EXISTS (SELECT index_name FROM information_schema.statistics  WHERE table_schema = database() AND table_name = ''',TABLE_NAME,''' AND index_name = ''',CONSTRAINT_NAME,''') THEN DROP INDEX ',CONSTRAINT_NAME,' ON ',TABLE_NAME, '; END IF;'        
) AS ''
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA=database()
AND COLUMN_NAME in ('olbs_key','olbs_type')
AND CONSTRAINT_NAME NOT IN (
       SELECT CONSTRAINT_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE CONSTRAINT_SCHEMA=database()
AND COLUMN_NAME NOT IN ('olbs_key','olbs_type'));

# drop indices referencing user and ref_data tables

SELECT CONCAT('IF EXISTS (SELECT index_name FROM information_schema.statistics WHERE table_schema = database() AND table_name = ''',i.TABLE_NAME,''' AND index_name = ''',i.INDEX_NAME,''') THEN DROP INDEX ',i.INDEX_NAME,' ON ',i.TABLE_NAME,'; END IF;') AS ''
FROM  information_schema.statistics i
WHERE i.table_schema = database()
AND i.INDEX_NAME NOT IN ('PRIMARY')
AND i.COLUMN_NAME IN ( SELECT COLUMN_NAME
               FROM information_schema.KEY_COLUMN_USAGE
               WHERE CONSTRAINT_SCHEMA=database()
               AND REFERENCED_TABLE_NAME in (
		'ref_data',
		'user',
		'category',
		'sub_category',
		'statement',
		'event_history_type',
		'fee_type',
		'opposer',
		'presiding_tc',
		'reason',
		'propose_to_revoke',
		'publication_section',
		'impounding',
		'country',
		'permission',
		'bus_notice_period',
		'local_authority',
		'irfo_gv_permit_type'
		)
               AND TABLE_NAME = i.TABLE_NAME
               AND COLUMN_NAME = i.COLUMN_NAME
               AND CONSTRAINT_NAME like 'fk_%');


SELECT CONCAT('IF EXISTS (SELECT index_name FROM information_schema.statistics WHERE table_schema = database() AND table_name = ''',i.TABLE_NAME,''' AND index_name = ''',i.INDEX_NAME,''') THEN DROP INDEX ',i.INDEX_NAME,' ON ',i.TABLE_NAME,'; END IF;') AS ''
FROM  information_schema.statistics i
WHERE i.table_schema = database()
AND i.INDEX_NAME IN (
'ix_cases_olbs_key_olbs_type',
'ix_cases_read_audit_created_on',
'ix_application_read_audit_created_on',
'uk_bus_reg_reg_no_variation_no_deleted_date',
'ix_bus_reg_read_audit_created_on',
'uk_bus_reg_traffic_area_olbs_key_traffic_area_id',
'ix_bus_reg_traffic_area_olbs_key_traffic_area_id',
'ix_bus_reg_variation_reason_olbs_key_olbs_key2',
'uk_company_subsidiary_olbs_key_licence_id',
'ix_complaint_olbs_key',
'ix_continuation_traffic_area_id_year_month',
'ix_ebsr_route_reprint_olbs_key',
'uk_event_history_type_event_code',
'ix_goods_disc_ceased_date',
'ix_goods_disc_issued_date',
'ix_licence_read_audit_created_on',
'ix_licence_vehicle_vi_action',
'uk_operating_centre_opposition_olbs_oc_id_olbs_opp_id_olbs_type',
'ix_organisation_name',
'ix_organisation_read_audit_created_on',
'ix_person_family_name',
'ix_person_forename',
'uk_postcode_enforcement_area_enforcement_area_id_postcode_id',
'uk_propose_to_revoke_case_id',
'uk_team_name',
'uk_ta_enforcement_area_traffic_area_id_enforcement_area_id',
'ix_txn_olbs_key',
'uk_user_pid',
'uk_user_login_id',
'ix_user_team_id',
'ix_vehicle_vrm',
'ix_vehicle_vi_action'
);

SELECT 'END' AS '';
SELECT '\$\$' AS '';


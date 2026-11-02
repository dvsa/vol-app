SELECT 'DROP PROCEDURE IF EXISTS sp_drop_indices;' AS '';
SELECT 'DELIMITER $$' AS '';
SELECT 'CREATE PROCEDURE sp_drop_indices()' AS '';
SELECT 'BEGIN' AS '';

# drop indices referencing user and ref_data tables

SELECT CONCAT(
    'IF EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'', i.TABLE_NAME, '\' AND index_name = \'', i.INDEX_NAME, '\') THEN ',
    'DROP INDEX ', i.INDEX_NAME, ' ON ', i.TABLE_NAME, '; END IF;'
) AS ''
FROM information_schema.statistics i
JOIN information_schema.KEY_COLUMN_USAGE kcu
  ON kcu.CONSTRAINT_SCHEMA = i.TABLE_SCHEMA
  AND kcu.TABLE_NAME = i.TABLE_NAME
  AND kcu.COLUMN_NAME = i.COLUMN_NAME
WHERE i.TABLE_SCHEMA = DATABASE()
  AND i.INDEX_NAME != 'PRIMARY'
  AND kcu.CONSTRAINT_NAME LIKE 'fk\_%'
  AND kcu.REFERENCED_TABLE_NAME IN (
    'ref_data', 'user', 'category', 'sub_category', 'statement',
    'event_history_type', 'fee_type', 'opposer', 'presiding_tc',
    'reason', 'propose_to_revoke', 'publication_section', 'impounding',
    'country', 'permission', 'bus_notice_period', 'local_authority',
    'irfo_gv_permit_type'
  )
GROUP BY i.TABLE_NAME, i.INDEX_NAME;

SELECT CONCAT(
    'IF EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'', i.TABLE_NAME, '\' AND index_name = \'', i.INDEX_NAME, '\') THEN ',
    'DROP INDEX ', i.INDEX_NAME, ' ON ', i.TABLE_NAME, '; END IF;'
) AS ''
FROM information_schema.statistics i
WHERE i.TABLE_SCHEMA = DATABASE()
  AND i.INDEX_NAME IN (
    'uk_bus_reg_reg_no_variation_no_deleted_date', 'ix_bus_reg_read_audit_created_on',
    'uk_event_history_type_event_code', 'ix_goods_disc_ceased_date', 'ix_goods_disc_issued_date',
    'ix_licence_read_audit_created_on', 'ix_licence_vehicle_vi_action',
    'ix_organisation_read_audit_created_on', 'ix_person_family_name', 'ix_person_forename',
    'uk_postcode_enforcement_area_enforcement_area_id_postcode_id', 'uk_propose_to_revoke_case_id',
    'uk_user_pid', 'uk_user_login_id', 'ix_user_team_id', 'ix_vehicle_vrm', 'ix_vehicle_vi_action'
  )
GROUP BY i.TABLE_NAME, i.INDEX_NAME;

SELECT 'END$$' AS '';
SELECT 'DELIMITER ;' AS '';

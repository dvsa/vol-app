SELECT 'DROP PROCEDURE IF EXISTS sp_add_NI_Extract_constraints;\nDELIMITER $$\nCREATE PROCEDURE sp_add_NI_Extract_constraints()\nBEGIN\nSET FOREIGN_KEY_CHECKS=0;' AS '';

SELECT CONCAT(
    'ALTER TABLE ', a.TABLE_NAME, 
    ' ADD CONSTRAINT ', a.CONSTRAINT_NAME, 
    ' FOREIGN KEY (', 
    GROUP_CONCAT(a.COLUMN_NAME ORDER BY a.ORDINAL_POSITION SEPARATOR ', '), 
    ') REFERENCES ', a.REFERENCED_TABLE_NAME, 
    ' (', 
    GROUP_CONCAT(a.REFERENCED_COLUMN_NAME ORDER BY a.ORDINAL_POSITION SEPARATOR ', '), 
    ') ', a.RULE_STRING, ';'
) AS ''
FROM (
    SELECT 
        kcu.TABLE_NAME, 
        kcu.CONSTRAINT_NAME, 
        kcu.COLUMN_NAME, 
        kcu.ORDINAL_POSITION, 
        kcu.REFERENCED_TABLE_NAME, 
        kcu.REFERENCED_COLUMN_NAME,
        CONCAT('ON DELETE CASCADE ON UPDATE ', rc.UPDATE_RULE) AS RULE_STRING
    FROM information_schema.KEY_COLUMN_USAGE kcu
    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
        ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA 
        AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
    WHERE kcu.CONSTRAINT_SCHEMA = DATABASE()
      AND kcu.CONSTRAINT_NAME != 'fk_bus_reg_parent_id_bus_reg_id'
      AND kcu.TABLE_NAME NOT IN ('user', 'ref_data')
      AND kcu.REFERENCED_TABLE_NAME NOT IN (
          'user', 'ref_data', 'category', 'sub_category', 'statement', 
          'event_history_type', 'fee_type', 'opposer', 'presiding_tc', 
          'reason', 'propose_to_revoke', 'publication_section', 'impounding', 
          'country', 'permission', 'bus_notice_period', 'local_authority', 
          'irfo_gv_permit_type'
      )

    UNION ALL

    SELECT 
        kcu.TABLE_NAME, 
        kcu.CONSTRAINT_NAME, 
        kcu.COLUMN_NAME, 
        kcu.ORDINAL_POSITION, 
        kcu.REFERENCED_TABLE_NAME, 
        kcu.REFERENCED_COLUMN_NAME,
        CONCAT('ON DELETE SET NULL ON UPDATE ', rc.UPDATE_RULE) AS RULE_STRING
    FROM information_schema.KEY_COLUMN_USAGE kcu
    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
        ON kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA 
        AND kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
    WHERE kcu.CONSTRAINT_SCHEMA = DATABASE()
      AND kcu.CONSTRAINT_NAME IN (
          'fk_bus_reg_parent_id_bus_reg_id',
          'fk_user_transport_manager_id_transport_manager_id',
          'fk_user_partner_contact_details_id_contact_details_id',
          'fk_user_contact_details_id_contact_details_id'
      )
) a
GROUP BY a.TABLE_NAME, a.CONSTRAINT_NAME, a.REFERENCED_TABLE_NAME, a.RULE_STRING
ORDER BY a.TABLE_NAME;

SELECT 'SET FOREIGN_KEY_CHECKS=1;\nEND\n$$' AS '';
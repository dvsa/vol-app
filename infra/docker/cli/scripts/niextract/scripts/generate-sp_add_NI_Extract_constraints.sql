SELECT 'DROP PROCEDURE IF EXISTS sp_add_NI_Extract_constraints;' AS '';
SELECT 'DELIMITER \$\$' AS '';
SELECT 'CREATE PROCEDURE sp_add_NI_Extract_constraints()' AS '';
SELECT 'BEGIN' AS '';

SELECT 'SET FOREIGN_KEY_CHECKS=0;' AS '';
select a.CMD AS '' from
( SELECT kcu.TABLE_NAME,CONCAT('ALTER TABLE ', kcu.TABLE_NAME,' ADD CONSTRAINT ',kcu.CONSTRAINT_NAME,' FOREIGN KEY (',kcu.COLUMN_NAME,') REFERENCES ',kcu.REFERENCED_TABLE_NAME,' (',kcu.REFERENCED_COLUMN_NAME,') ON DELETE CASCADE ON UPDATE ',rc.UPDATE_RULE,';') AS CMD
 from INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
    ON kcu.TABLE_CATALOG = rc.CONSTRAINT_CATALOG  
    AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA 
    AND kcu.CONSTRAINT_NAME =rc.CONSTRAINT_NAME
    AND kcu.constraint_schema=database()
JOIN information_schema.columns c
ON c.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA 
AND c.TABLE_NAME = kcu.TABLE_NAME
AND c.COLUMN_NAME = kcu.COLUMN_NAME
AND kcu.CONSTRAINT_NAME not in ('fk_bus_reg_parent_id_bus_reg_id')
AND kcu.REFERENCED_TABLE_NAME not in
('user',
'ref_data',
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
AND kcu.TABLE_NAME not in ('user','ref_data')
UNION
SELECT kcu.TABLE_NAME,CONCAT('ALTER TABLE ', kcu.TABLE_NAME,' ADD CONSTRAINT ',kcu.CONSTRAINT_NAME,' FOREIGN KEY (',kcu.COLUMN_NAME,') REFERENCES ',kcu.REFERENCED_TABLE_NAME,' (',kcu.REFERENCED_COLUMN_NAME,') ON DELETE SET NULL ON UPDATE ',rc.UPDATE_RULE,';') AS CMD
 from INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
    ON kcu.TABLE_CATALOG = rc.CONSTRAINT_CATALOG  
    AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA 
    AND kcu.CONSTRAINT_NAME =rc.CONSTRAINT_NAME
    AND kcu.constraint_schema=database()
JOIN information_schema.columns c
ON c.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA 
AND c.TABLE_NAME = kcu.TABLE_NAME
AND  kcu.CONSTRAINT_NAME in (
'fk_bus_reg_parent_id_bus_reg_id',
'fk_user_transport_manager_id_transport_manager_id',
'fk_user_partner_contact_details_id_contact_details_id',
'fk_user_contact_details_id_contact_details_id'
)
AND c.COLUMN_NAME = kcu.COLUMN_NAME ) a
ORDER BY a.TABLE_NAME ;
SELECT 'SET FOREIGN_KEY_CHECKS=1;' AS '';
SELECT 'END' AS '';
SELECT '\$\$' AS '';

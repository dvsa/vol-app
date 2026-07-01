SELECT CONCAT('DROP PROCEDURE IF EXISTS sp_NI_Extract_update_table_counts;', CHAR(10), 'CREATE PROCEDURE sp_NI_Extract_update_table_counts()', CHAR(10), 'BEGIN') AS '';

SELECT CONCAT(
    'UPDATE NI_Extract, (SELECT COUNT(*) AS cnt FROM ', t.TABLE_NAME, ') AS src ',
    'SET POST_EXTRACT_COUNT = src.cnt, LAST_MODIFIED_ON = NOW() ',
    'WHERE TABLE_NAME = \'', t.TABLE_NAME, '\';'
) AS ''
FROM information_schema.TABLES t
WHERE t.TABLE_SCHEMA = DATABASE()
  AND t.TABLE_TYPE = 'BASE TABLE'
  AND t.TABLE_NAME NOT LIKE '%\_hist'
  AND t.TABLE_NAME != 'NI_Extract'
ORDER BY t.TABLE_NAME;

SELECT CONCAT('END', CHAR(10), '$$') AS '';

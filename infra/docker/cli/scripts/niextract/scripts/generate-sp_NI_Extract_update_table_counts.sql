SELECT 'DROP PROCEDURE IF EXISTS sp_NI_Extract_update_table_counts;' AS '';
SELECT 'DELIMITER $$' AS '';
SELECT 'CREATE PROCEDURE sp_NI_Extract_update_table_counts()' AS '';
SELECT 'BEGIN' AS '';

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

SELECT 'END$$' AS '';
SELECT 'DELIMITER ;' AS '';

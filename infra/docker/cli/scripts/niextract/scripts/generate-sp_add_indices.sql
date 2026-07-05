SELECT CONCAT('DROP PROCEDURE IF EXISTS sp_add_indices', CHAR(10), '$$', CHAR(10), 'CREATE PROCEDURE sp_add_indices()', CHAR(10), 'BEGIN') AS '';

SELECT CONCAT(
    'IF NOT EXISTS (SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = \'', SUBSTRING(t.NAME, LENGTH(DATABASE()) + 2), '\' AND index_name = \'', i.NAME, '\') THEN ',
    'CREATE ', IF(i.TYPE = 2, 'UNIQUE ', ''), 'INDEX ', i.NAME, ' ON ', SUBSTRING(t.NAME, LENGTH(DATABASE()) + 2), ' (', cols.COLS, '); END IF;'
) AS ''
FROM information_schema.INNODB_INDEXES i
JOIN information_schema.INNODB_TABLES t ON i.TABLE_ID = t.TABLE_ID
JOIN (
    SELECT INDEX_ID,
           GROUP_CONCAT(NAME ORDER BY POS SEPARATOR ', ') AS COLS
    FROM information_schema.INNODB_FIELDS
    GROUP BY INDEX_ID
) cols ON i.INDEX_ID = cols.INDEX_ID
WHERE t.NAME LIKE CONCAT(DATABASE(), '/%')
  AND t.NAME NOT LIKE '%\_hist'
  AND i.TYPE != 3 # NOT PRIMARY
ORDER BY t.NAME;

SELECT CONCAT('END', CHAR(10), '$$') AS '';

SELECT 'DROP PROCEDURE IF EXISTS sp_add_indices;' AS '';
SELECT 'DELIMITER \$\$' AS '';
SELECT 'CREATE PROCEDURE sp_add_indices()' AS '';
SELECT 'BEGIN' AS '';
SELECT CONCAT('IF NOT EXISTS (SELECT index_name FROM information_schema.statistics WHERE table_schema = database() AND table_name = ''',right(t.NAME,LENGTH(t.NAME)-LENGTH(database())-1),''' AND index_name = ''',i.NAME,''') THEN CREATE ',(SELECT CASE TYPE WHEN 2 THEN 'UNIQUE  ' ELSE '' END),'INDEX ',i.NAME,' ON ',right(t.NAME,LENGTH(t.NAME)-LENGTH(database())-1),' (',cols.COLS,'); END IF;') AS ''
FROM information_schema.INNODB_INDEXES i
JOIN information_schema.INNODB_TABLES t ON i.TABLE_ID = t.TABLE_ID
JOIN (SELECT INDEX_ID,
             GROUP_CONCAT(NAME
             ORDER BY POS) AS COLS
     FROM information_schema.INNODB_FIELDS
     GROUP BY INDEX_ID) cols
WHERE t.NAME LIKE CONCAT(database(),'/%')
AND t.NAME NOT LIKE  '%_hist'
AND i.INDEX_ID = cols.INDEX_ID
AND i.TYPE NOT IN (3) # NOT PRIMARY
ORDER BY t.NAME;
SELECT 'END' AS '';
SELECT '\$\$' AS '';

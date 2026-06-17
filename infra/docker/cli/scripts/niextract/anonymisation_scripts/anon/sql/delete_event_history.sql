################################################################
#
#   delete old OLCS event history.
#
################################################################

SELECT CONCAT(now(), ' Fast optimizing event_history via table swap...') as '';

-- Create an identical empty structure of the table
CREATE TABLE event_history_new LIKE event_history;

-- Copy only the recent 6 years of data (Bulk INSERT is exponentially faster than DELETE)
INSERT INTO event_history_new
SELECT * FROM event_history WHERE event_datetime >= DATE_SUB(NOW(), INTERVAL 6 YEAR);

-- Swap the tables instantly in the engine metadata
RENAME TABLE event_history TO event_history_old, event_history_new TO event_history;

-- Drop the old massive table to free up AWS cloud storage space
DROP TABLE event_history_old;

SELECT CONCAT(now(), ' ...event_history optimization complete.') as '';

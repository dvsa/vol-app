################################################################
#
#   delete old OLCS event history.
#
################################################################

SELECT CONCAT(now(), ' Fast optimizing event_history via table swap...') as '';

-- Create an identical structure
CREATE TABLE event_history_new LIKE event_history;

-- Copy only the recent 6 years of data
INSERT INTO event_history_new
SELECT * FROM event_history WHERE event_datetime >= DATE_SUB(NOW(), INTERVAL 6 YEAR);

-- RE-ADD MISSING FOREIGN KEYS (Adjust constraint names/columns to match your exact schema)
ALTER TABLE event_history_new 
  ADD CONSTRAINT `fk_event_history_user1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Swap the tables instantly
RENAME TABLE event_history TO event_history_old, event_history_new TO event_history;

-- Drop the old massive table
DROP TABLE event_history_old;

SELECT CONCAT(now(), ' ...event_history optimization complete.') as '';

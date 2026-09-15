################################################################
#
#   delete old OLCS event history.
#
################################################################

SELECT CONCAT(now(), ' Fast optimizing event_history via table swap...') as '';

-- Create an identical structure
DROP TABLE IF EXISTS event_history_new;
DROP TABLE IF EXISTS event_history_old;
CREATE TABLE event_history_new LIKE event_history;

-- Copy only the recent 6 years of data
INSERT INTO event_history_new
SELECT * FROM event_history WHERE event_datetime >= DATE_SUB(NOW(), INTERVAL 6 YEAR);

-- Swap the tables instantly
RENAME TABLE event_history TO event_history_old, event_history_new TO event_history;

-- Drop the old massive table
DROP TABLE event_history_old;

-- Re-add original foreign keys (must happen after dropping the old table to avoid FK name conflicts)
ALTER TABLE event_history
  ADD CONSTRAINT fk_event_history_event_history_type_id_event_history_type_id FOREIGN KEY (event_history_type_id) REFERENCES event_history_type (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_user_id_user_id FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_licence_id_licence_id FOREIGN KEY (licence_id) REFERENCES licence (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_application_id_application_id FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_transport_manager_id_transport_manager_id FOREIGN KEY (transport_manager_id) REFERENCES transport_manager (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_organisation_id_organisation_id FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_case_id_cases_id FOREIGN KEY (case_id) REFERENCES cases (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_bus_reg_id_bus_reg_id FOREIGN KEY (bus_reg_id) REFERENCES bus_reg (id) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT fk_event_history_account_id_user_id FOREIGN KEY (account_id) REFERENCES `user` (id) ON DELETE NO ACTION ON UPDATE NO ACTION;

SELECT CONCAT(now(), ' ...event_history optimization complete.') as '';

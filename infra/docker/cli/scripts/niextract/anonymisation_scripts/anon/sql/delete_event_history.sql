################################################################
#
#   delete old OLCS event history.
#
################################################################

SELECT CONCAT(now(), ' delete old event_history...') as '';

DELETE FROM event_history WHERE event_datetime < DATE_SUB(NOW(), INTERVAL 6 YEAR);

SELECT CONCAT(now(), ' ...delete old event_history complete.') as '';

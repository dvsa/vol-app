/*
 This file will queue up the retrieval of company data for all active
 organisations already in the db
*/

SET foreign_key_checks = 0;

START TRANSACTION;

TRUNCATE TABLE `queue`;
TRUNCATE TABLE `companies_house_officer`;
TRUNCATE TABLE `companies_house_company`;

INSERT INTO `queue` (`status`, `type`, `options`)
SELECT DISTINCT 'que_sts_queued',
                'que_typ_ch_initial',
                CONCAT('{"companyNumber":"', o.company_or_llp_no, '"}')
FROM organisation o
INNER JOIN licence l ON o.id=l.organisation_id
WHERE l.status IN ('lsts_consideration',
                   'lsts_suspended',
                   'lsts_valid',
                   'lsts_curtailed',
                   'lsts_granted')
  AND o.company_or_llp_no IS NOT NULL
ORDER BY o.company_or_llp_no;

COMMIT;

SET foreign_key_checks = 1;
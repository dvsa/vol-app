-- VOL REG TEST DATA/EDH LINK UP

-- ABC381
SELECT CONCAT(now(),' updating ABC381...') as '';
SELECT *
FROM vehicle v
where v.id = '100001';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100001';

SELECT *
FROM licence l
where l.id = '267413';

UPDATE licence l
SET l.lic_no = 'PY2001264',
cns_date = NULL,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '267413';

UPDATE vehicle v
SET v.vrm = 'ABC381'
WHERE v.id='100001';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='267413';


-- ABC382
SELECT CONCAT(now(),' updating ABC382...') as '';
SELECT *
FROM vehicle v
where v.id = '100002';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100002';

SELECT *
FROM licence l
where l.id='19909';

UPDATE licence l
SET l.lic_no='PY2001265',
cns_date = NULL,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id='19909';

UPDATE vehicle v
SET v.vrm='ABC382'
WHERE v.id='100002';

UPDATE licence_vehicle lv
SET removal_date=NULL
WHERE lv.id='123885';


-- ABC383
SELECT CONCAT(now(),' updating ABC383...') as '';
SELECT *
FROM vehicle v
where v.id='100005';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id='100005';

SELECT *
FROM licence l
where l.id='22462';

UPDATE licence l
SET l.lic_no='PY2001266',
cns_date = NULL,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status='lsts_valid'
WHERE l.id='22462';

UPDATE vehicle v
SET v.vrm='ABC383'
WHERE v.id='100005';

UPDATE licence_vehicle lv
SET removal_date=NULL
WHERE lv.id='86170';


-- ABC384
SELECT CONCAT(now(),' updating ABC384...') as '';
SELECT *
FROM vehicle v
where v.id='100011';

SELECT*
FROM licence_vehicle lv
where lv.vehicle_id='100011';

SELECT *
FROM licence l
where l.id='21490';

UPDATE licence l
SET l.lic_no='PY2001267',
cns_date = NULL,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id='21490';

UPDATE vehicle v
SET v.vrm='ABC384'
WHERE v.id='100011';

UPDATE licence_vehicle lv
SET removal_date=NULL
WHERE lv.id='99732';


-- ABC385
SELECT CONCAT(now(),' updating ABC385...') as '';
SELECT *
FROM vehicle v
where v.id='100017';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id='100017';

SELECT *
FROM licence l
where l.id='22893';

UPDATE licence l
SET l.lic_no='PY2001268',
cns_date = NULL,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id='22893';

UPDATE vehicle v
SET v.vrm='ABC385'
WHERE v.id='100017';

UPDATE licence_vehicle lv
SET removal_date=NULL
WHERE lv.id='84802';


-- ABC387
SELECT CONCAT(now(),' updating ABC387...') as '';
SELECT *
FROM vehicle v
where v.id = '100025';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100025';

SELECT *
FROM licence l
where l.id = '22633';

UPDATE licence l
SET l.lic_no = 'OF1000049',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '22633';

UPDATE vehicle v
SET v.vrm = 'ABC387'
WHERE v.id='100025';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='1365238';


-- ABC389
SELECT CONCAT(now(),' updating ABC389...') as '';
SELECT *
FROM vehicle v
where v.id = '100039';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100039';

SELECT *
FROM licence l
where l.id = '20092';

UPDATE licence l
SET l.lic_no = 'OK1000044',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '20092';

UPDATE vehicle v
SET v.vrm = 'ABC389'
WHERE v.id='100039';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='117746';


-- ABC411
SELECT CONCAT(now(),' updating ABC411...') as '';
SELECT *
FROM vehicle v
where v.id = '100029';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100029';

SELECT *
FROM licence l
where l.id = '22611';

UPDATE licence l
SET l.lic_no = 'PM1000029',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '22611';

SELECT *
FROM licence l
where l.lic_no = 'PM1000029';

UPDATE vehicle v
SET v.vrm = 'ABC411'
WHERE v.id='100029';


UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='73877';


-- ABC412
SELECT CONCAT(now(),' updating ABC412...') as '';
SELECT *
FROM vehicle v
where v.id = '100030';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100030';

SELECT *
FROM licence l
where l.id = '116412';

UPDATE licence l
SET l.lic_no = 'PF1000037',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '116412';

UPDATE vehicle v
SET v.vrm = 'ABC412'
WHERE v.id='100030';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='461522';


-- ABC413
SELECT CONCAT(now(),' updating ABC413...') as '';
SELECT *
FROM vehicle v
where v.id = '100031';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100031';

SELECT *
FROM licence l
where l.id = '22238';

UPDATE licence l
SET l.lic_no = 'PH1000032',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '22238';

UPDATE vehicle v
SET v.vrm = 'ABC413'
WHERE v.id='100031';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='86974';


-- PY2001276
SELECT CONCAT(now(),' updating PY2001276...') as '';
SELECT *
FROM vehicle v
where v.id = '100092';

SELECT *
FROM licence_vehicle lv
where lv.vehicle_id = '100092';

SELECT *
FROM licence l
where l.id = '21589';

UPDATE licence l
SET l.lic_no = 'PY2001276',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '21589';

SELECT *
FROM licence l
where l.lic_no = 'PY2001276';

UPDATE licence_vehicle lv
SET removal_date = NULL
WHERE lv.id ='89496';


-- PY2001277 (operator only)
SELECT CONCAT(now(),' updating PY2001277...') as '';
SELECT *
FROM licence l
where l.id = '891';

select * from licence l where lic_no = 'PY2001277';

UPDATE licence l
SET l.lic_no = 'PY2001277',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '891';


-- PY2001278 (operator only)
SELECT CONCAT(now(),' updating PY2001278...') as '';
SELECT *
FROM licence l
where l.id = '1476';

select * from licence l where lic_no = 'PY2001278';

UPDATE licence l
SET l.lic_no = 'PY2001278',
cns_date = NULL,
enforcement_area_id = null,
review_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
expiry_date = DATE_ADD(NOW(), INTERVAL 5 YEAR),
status = 'lsts_valid'
WHERE l.id = '1476';
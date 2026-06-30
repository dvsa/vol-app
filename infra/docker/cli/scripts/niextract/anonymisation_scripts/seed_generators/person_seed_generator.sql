DROP TABLE IF EXISTS name_seed;

CREATE TABLE name_seed(
	id int,
	forename varchar(50), 
	family_name varchar(50));

LOAD DATA LOCAL INFILE '/media/sf_OLCS/temp/names.csv' INTO TABLE name_seed
FIELDS TERMINATED BY ',';

SET @row_count= (select count(*) from person) + 1;

    select 
    concat('update person set forename="', name_seed.forename, '",family_name="', name_seed.family_name, '" where id=', tx.id, ';') as xyz

    from name_seed
    join 
        (select @row_count:=@row_count-1 as rownum, id
        from (
            select * from person  order by id
            ) ty) tx
	on tx.rownum = name_seed.id

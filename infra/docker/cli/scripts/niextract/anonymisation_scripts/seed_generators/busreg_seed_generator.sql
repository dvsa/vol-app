DROP TABLE IF EXISTS bus_reg_seed;
CREATE TABLE bus_reg_seed(
	id int,
	start_point varchar(100),
	finish_point varchar(100));

LOAD DATA LOCAL INFILE '/media/sf_OLCS/temp/busregs.csv' INTO TABLE bus_reg_seed
FIELDS TERMINATED BY ','
		ENCLOSED BY '"';


SET @row_count= (select count(*) from bus_reg) + 1;

    select 
    concat('update bus_reg set start_point="', bus_reg_seed.start_point, '",finish_point="', bus_reg_seed.finish_point, '" where id=', tx.id, ';') as xyz

    from bus_reg_seed
    join 
        (select @row_count:=@row_count-1 as rownum, id
        from (
            select * from bus_reg  order by id
            ) ty) tx
	on tx.rownum = bus_reg_seed.id

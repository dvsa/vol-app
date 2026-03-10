DROP TABLE IF EXISTS trading_name_seed;
CREATE TABLE trading_name_seed(
	id int,
	trading_name varchar(160));

LOAD DATA LOCAL INFILE '/media/sf_OLCS/temp/tradingnames.csv' INTO TABLE trading_name_seed
FIELDS TERMINATED BY ',';

SET @row_count= (select count(*) from trading_name) + 1;

    select 
    concat('update trading_name set name="', trading_name_seed.trading_name, '" where id=', tx.id, ';') as xyz

    from trading_name_seed
    join 
        (select @row_count:=@row_count-1 as rownum, id
        from (
            select * from trading_name  order by id
            ) ty) tx
	on tx.rownum = trading_name_seed.id

(use -c "$connection" option to override default db connection details provided by ~/.my.cnf)
 
1.	to run default anonymisation (with older history deleted):

	./run_anonymisation.sh -d <db> -f <temp file dir> 

2.	to run anonymisation and keep full history:

	./run_anonymisation.sh -d <db -f <temp file dir> -H

3.	to run NI anonymisation version:

	./run_anonymisation.sh -d <db> -f <temp file dir> -N

4.	to force anonymisation despite a schema check failure, add the -F flag
	(the script will exit if schema changes are detected by default)

	./run_anonymisation.sh -d  <db> -f <temp file dir> -H -F

5.	to dump default schema csv file to use as basis for the schema check (useful for testing):

	./run_anonymisation.sh -d <db> -D
 
6.	to use alternate file for schema check, use -S flag:
	(olcs_schema_ref.csv is used as default)

	./run_anonymisation.sh -d <db> -f <temp file dir> -H  -S <alternate schema ref file>

7.	to enable then disable local_infile on the target mysql server, use -I flag:

	./run_anonymisation.sh -d <db> -f <temp file dir> -I

NOTES:
======
1.	dump anon db in format olcs-db-anon-prod-YYYY-MM-DD.sql.gz :
	mysqldump --routines --triggers <anon_db> | gzip -9 > <PATH>/olcs-db-anon-prod-YYYY-MM-DD.sql.gz

2.	anon s3 bucket is s3://devapp-olcs-pri-olcs-deploy-s3/anondata

3.	you'll need around 1GB of disk space for the temp data files.

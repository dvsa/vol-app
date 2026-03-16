## Set up and run NI Extract ##

There are two run scripts that call NI_Extract.sh:

run_NI_Extract.sh - runs NI Extract only
-----------------

./run_NI_Extract.sh  <Database> [ "CONTINUE" ]

run_NI_Extract-Anon.sh - runs NI Extract followed by NI Anonymisation and creation of XML db dump file.
----------------------

./run_NI_Extract-Anon.sh  <Database> <Anon Home Dir> <Anon temp file dir> [ "CONTINUE" ]

if "CONTINUE" is set then this will continue running the existing extract, meaning that the all the pre-run tasks are skipped.

NI_Extract.sh Description
-------------------------

(mysql connection string is optional and if not supplied ~/.my.cnf is used by default)

before actually running the NI Extract there are several tasks required to the prepare the database:

1. generate stored procedure scripts by running:

./generate_procedure_scripts.sh "$connection" <db>

this creates the following sp scripts:

sp_drop_constraints.sql - alter table drop FKs.
sp_add_original_constraints.sql - original alter table add FKs.
sp_add_NI_Extract_constraints.sql - updated alter table add FKs for NI Extract run.

sp_drop_indices.sql - drop indices not required for extract
sp_add_indices.sql - replace same indices after extract

sp_drop_hist_tables.sql - drop all %_hist tables.
sp_drop_triggers.sql - drop all triggers.

sp_NI_Extract_save_table_counts.sql - populate NI_Extract table with initial table counts.
sp_NI_Extract_update_table_counts.sql - populate NI_Extract table with final table counts following NI Extract run.

sp_validate_NI_Extract - validate tables after extract completes

2. Add procedures and table to database

./install_NI_Extract_db_objects.sh "$connection" <db>

this installs:

NI_Extract table
sp_NI_Extract - main NI Extract controlling procedure.
sp_drop_constraints.sql
sp_add_original_constraints.sql
sp_add_NI_Extract_constraints.sql
sp_drop_hist_tables.sql
sp_drop_triggers.sql
sp_NI_Extract_save_table_counts.sql
sp_NI_Extract_update_table_counts.sql
sp_delete* procedures called by sp_NI_Extract

3. Now run pre-extract:
./run-pre-NI-Extract.sh "$connection" OLCS_NI

this does the following:
- drop all constraints
- add required constraints for extract
- drop unwanted indices
- drop all _hist tables
- drop all triggers

4. Now NI Extract can be run by calling sp_NI_Extract procedure (takes approx 10 hours to run)
The procedure parameter controls whether or not to populate the NI_Extract summary table depending if the NI Extract is a restart of existing run or not.

5. Next, the post extract script is run:

./run-post-NI-Extract.sh "$connection" OLCS_NI

this puts the db back to its original state in terms of indices and fk constraints:

6. if option is set, NI anonymisation is run:

cd <Anon home dir>
./run_anonymisation.sh -c "$CONNECTION" -d <db> -f <anon temp file dir> -N

(-N indicates that the NI flavour of anonymisation process should be run.)

7. Once complete the validation script runs:

./run_validate_NI_Extract.sh "$connection" OLCS_NI

this does a few integrity checks of the main tables

8. Now drop NI Extract procedures and tables:

./uninstall_NI_Extract_db_objects.sh "$connection" OLCS_NI

9. Finally, if option is set, a gzip XML dump file of the anon db is created. 

 

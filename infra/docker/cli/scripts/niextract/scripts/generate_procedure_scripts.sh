#!/bin/env bash

CONNECTION=$1
DB=$2

# constraint procedure scripts
mysql $CONNECTION -e "use $DB;\. generate-sp_drop_constraints.sql" | tr -d '/-/' > sp_drop_constraints.sql
mysql $CONNECTION -e "use $DB;\. generate-sp_add_original_constraints.sql" | tr -d '/-/' > sp_add_original_constraints.sql
mysql $CONNECTION -e "use $DB;\. generate-sp_add_NI_Extract_constraints.sql" | tr -d '/-/' > sp_add_NI_Extract_constraints.sql

#NI_Extract table maintainenance scripts
mysql $CONNECTION -e "use $DB;\. generate-sp_NI_Extract_save_table_counts.sql" | tr -d '/-/' > sp_NI_Extract_save_table_counts.sql
mysql $CONNECTION -e "use $DB;\. generate-sp_NI_Extract_update_table_counts.sql" | tr -d '/-/' > sp_NI_Extract_update_table_counts.sql

#drop triggers
mysql $CONNECTION -e "use $DB;\. generate-sp_drop_triggers.sql" | tr -d '/-/' > sp_drop_triggers.sql

#drop _hist tables
mysql $CONNECTION -e "use $DB;\. generate-sp_drop_hist_tables.sql" | tr -d '/-/' > sp_drop_hist_tables.sql

# drop indices
mysql $CONNECTION -e "use $DB;\. generate-sp_drop_indices.sql" | tr -d '/-/' > sp_drop_indices.sql

# add indices
mysql $CONNECTION -e "use $DB;\. generate-sp_add_indices.sql" | tr -d '/-/' > sp_add_indices.sql


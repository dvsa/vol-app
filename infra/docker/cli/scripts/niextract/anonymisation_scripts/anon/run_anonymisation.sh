#! /bin/env bash

#####################################################################
#							            #
# anonymisation run script.				   	    #
#							   	    #
#####################################################################
SQL_DIR="./sql"
DATA_DIR="./data"

PERSON_DATA_FILE="person.dat"
PERSON_ANON_DATA_FILE="anon_person.dat"
POLICE_DATA_FILE="police.dat"
POLICE_ANON_DATA_FILE="anon_police.dat"
NAMES_CSV_FILE="$DATA_DIR/names.csv"
ADDRESS_DATA_FILE="address.dat"
ADDRESS_ANON_DATA_FILE="anon_address.dat"
ADDRESS_CSV_FILE="$DATA_DIR/address.csv"
COMPANIES_DATA_FILE="companies_house_company.dat"
COMPANIES_ANON_DATA_FILE="anon_companies_house_company.dat"
SUBSIDIARY_DATA_FILE="company_subsidiary.dat"
SUBSIDIARY_ANON_DATA_FILE="anon_company_subsidiary.dat"
COMPANIES_CSV_FILE="$DATA_DIR/companies.csv"
ANSWER_DATA_FILE="answer.dat"
ANSWER_ANON_DATA_FILE="anon_answer.dat"

DEFAULT_OLCS_SCHEMA_REFERENCE_FILE="olcs_schema_ref.csv"
CURRENT_OLCS_SCHEMA_FILE="current_OLCS_schema.csv"
OLCS_SCHEMA_SCRIPT="OLCS_schema.sql"

NI_ANONYMISATION_SQL_STEPS=(
create_populate_anonymise_text_table.sql
anonymise_NI_organisation.sql
source_change_of_entity_seed.sql
anonymise_names.sql
anonymise_member_organisation.sql
source_trading_name_seed.sql
anonymise_messaging_content.sql
anonymise_other_tables.sql
anonymise_email_address.sql
cleanup.sql
)

ANONYMISATION_SQL_STEPS=(
create_populate_anonymise_text_table.sql
source_companies_house_company_seed.sql
source_address_seed.sql
source_organisation_seed.sql
source_change_of_entity_seed.sql
anonymise_names.sql
anonymise_member_organisation.sql
source_person_seed.sql
source_trading_name_seed.sql
anonymise_bus_reg.sql
anonymise_other_tables.sql
anonymise_email_address.sql
anonymise_messaging_content.sql
anonymise_hist_tables.sql
add_search_data.sql
cleanup.sql
)

STEPS_IN_BACKGROUND=(
delete_event_history.sql
)

PIDS_IN_BACKGROUND=()

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

log () {
printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

warn () {
    echo -e "${RED}$1${NC}"
}

ok () {
    echo -e "${GREEN}$1${NC}"
}

log_error () {
log "$1"
exit 1
}

run_anonymise_person () {

    log "Start person anonymisation..."
    ./anonymise_person.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $PERSON_DATA_FILE $PERSON_ANON_DATA_FILE $NAMES_CSV_FILE || log_error "anonymise_person.sh FAILED!"
}

run_anonymise_publication_police_data () {

    log "Start publication_police_data anonymisation..."
    ./anonymise_publication_police_data.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $POLICE_DATA_FILE $POLICE_ANON_DATA_FILE $NAMES_CSV_FILE || log_error "anonymise_publication_police_data.sh FAILED!" 
}

run_anonymise_address () {

    log "Start address anonymisation..."
    ./anonymise_address.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $ADDRESS_DATA_FILE $ADDRESS_ANON_DATA_FILE $ADDRESS_CSV_FILE || log_error "anonymise_address.sh FAILED!" 
}

run_anonymise_companies_house_company () {

    log "Start companies_house_company anonymisation..."
    ./anonymise_companies_house_company.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $COMPANIES_DATA_FILE $COMPANIES_ANON_DATA_FILE $COMPANIES_CSV_FILE || log_error "anonymise_companies_house_company.sh FAILED!" 
}

run_anonymise_company_subsidiary () {

    log "Start company_subsidiary anonymisation..."
    ./anonymise_company_subsidiary.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $SUBSIDIARY_DATA_FILE $SUBSIDIARY_ANON_DATA_FILE $COMPANIES_CSV_FILE || log_error "anonymise_company_subsidiary.sh FAILED!" 
}

run_anonymise_generic_answer () {

    log "Start answer anonymisation..."
    ./anonymise_generic_answers.sh "$CONNECTION" $ANON_DB $ANON_DATA_DIR $ANSWER_DATA_FILE $ANSWER_ANON_DATA_FILE || log_error "anonymise_generic_answer.sh FAILED!"
}

enable_local_infile_on_server () {

    log "Enable local_infile on server"
    mysql $CONNECTION -e "SET GLOBAL local_infile = 'ON';"
}

disable_local_infile_on_server () {

    log "Disable local_infile on server"
    mysql $CONNECTION -e "SET GLOBAL local_infile = 'OFF';"
}

run_anonymise_sql () {

    # check that all steps running in background have completed
    check_steps_in_background_complete

    # run each sql anonymisation step

    for SQL_SCRIPT in "${ANONYMISATION_SQL_STEPS[@]}"
    do
        mysql --local-infile=1 $CONNECTION -e "use $ANON_DB;\. $SQL_DIR/$SQL_SCRIPT" || log_error "$SQL_DIR/$SQL_SCRIPT FAILED!"
    done
}

run_NI_anonymise_sql () {

    # run each sql anonymisation step

    for SQL_SCRIPT in "${NI_ANONYMISATION_SQL_STEPS[@]}"
    do
        mysql --local-infile=1 $CONNECTION -e "use $ANON_DB;\. $SQL_DIR/$SQL_SCRIPT" || log_error "$SQL_DIR/$SQL_SCRIPT FAILED!"
    done
}

run_steps_in_background () {

    if ($DELETE_OLD_HISTORY); then

        STEPS_IN_BACKGROUND+=( delete_history_part_1.sql delete_history_part_2.sql )
    fi

    # run each step in background

    for SQL_SCRIPT in "${STEPS_IN_BACKGROUND[@]}"
    do
        mysql --local-infile=1 $CONNECTION -e "use $ANON_DB;\. $SQL_DIR/$SQL_SCRIPT" &
        PID=$!
        log "Running $SQL_DIR/$SQL_SCRIPT in background (pid $PID)...."
        PIDS_IN_BACKGROUND+=( $PID )
    done
}

check_steps_in_background_complete () {

    # check that all steps running in background have completed

    for PID in "${PIDS_IN_BACKGROUND[@]}"
    do
        log "Checking a step running in background (pid $PID)...."

        while true; do

            kill -0 $PID 2>/dev/null

            if [ $? == 0 ]; then
                log "...waiting for the step running in background (pid $PID) to complete, will sleep for 60 seconds..."
                sleep 60
            else
                log "...the step running in background (pid $PID) has completed."
                break
            fi
        done
    done
}

usage () {

    if [ -n "$1" ]; then
       echo ERROR : $1
    fi

    echo "Usage $(basename $0) [-c <connection>] -d <database> -f <temporary files directory> [OPTIONS]"
    echo " -C - run schema check and exit"
    echo " -H - keep full history"
    echo " -N - anonymise for NI Extract"
    echo " -F - skip schema check"
    echo " -S <schema reference file> specify schema reference file for schema check"
    echo " -I - Enable and disable local_infile on server during run"
    echo " -h - display usage"
    exit
}

run_NI_anonymisation () {

    log "Start NI anonymisation..."

    run_anonymise_person
    run_anonymise_publication_police_data
    run_anonymise_address
    run_anonymise_company_subsidiary

    run_NI_anonymise_sql

    log "...NI anonymisation Complete."
}

run_anonymisation () {
  
    log "Start anonymisation..."

    run_steps_in_background

    run_anonymise_person
    run_anonymise_publication_police_data
    run_anonymise_address
    run_anonymise_companies_house_company
    run_anonymise_company_subsidiary
    run_anonymise_generic_answer
    run_anonymise_sql

    log "...anonymisation Complete."
}

dump_OLCS_schema () {

    CSV_FILE=$1

    # dump OLCS schema to csv file

    mysql --local-infile=1 $CONNECTION -NBe "use $ANON_DB;\. $SQL_DIR/$OLCS_SCHEMA_SCRIPT"  > $CSV_FILE || log_error "dumping OLCS schema to $CSV_FILE FAILED!"
    sed -i 's/\t/,/g' $CSV_FILE

}

check_OLCS_schema () {

    if [ ! -r $OLCS_SCHEMA_REFERENCE_FILE ]; then
        log_error "cannot check schema as reference schema file $OLCS_SCHEMA_REFERENCE_FILE not found!"
    fi

   log "checking for schema changes..."

    dump_OLCS_schema $CURRENT_OLCS_SCHEMA_FILE

    new_cols=();
    rem_cols=();
    OLDIFS=$IFS
    IFS=$','
    while read -r "table" "column"; do

        if [ $(grep -c "$table$IFS$column" $OLCS_SCHEMA_REFERENCE_FILE) -eq 0 ]; then
            new_cols+=("$table.$column")
        fi
    done < $CURRENT_OLCS_SCHEMA_FILE

    while read -r "table" "column"; do

        if [ $(grep -c "$table$IFS$column" $CURRENT_OLCS_SCHEMA_FILE) -eq 0 ]; then
            rem_cols+=("$table.$column")
        fi
    done < $OLCS_SCHEMA_REFERENCE_FILE

    IFS=$OLDIFS

    rm $CURRENT_OLCS_SCHEMA_FILE

    if [ ${#new_cols[@]} -gt 0 ] || [ ${#rem_cols[@]} -gt 0 ]; then
        clear
        warn "***********************************"
        warn "** WARNING WARNING WARNING !!!!! **"
        warn "***********************************"

        if [ ${#new_cols[@]} -gt 0 ]; then
            echo
            warn "The following new column(s) have been detected in the OLCS schema:"
            echo
            for i in "${new_cols[@]}" ;do
                warn "      $i"
            done

            echo
            warn "PLEASE CHECK IF NEW COLUMNS REQUIRE ANONYMISING BEFORE RUNNING ANONYMISATION PROCESS."
        fi

        if [ ${#rem_cols[@]} -gt 0 ]; then
            echo
            warn "The following column(s) have been removed from the OLCS schema:"
            echo
            for i in "${rem_cols[@]}" ;do
                warn "      $i"
            done

            echo
            warn "PLEASE CHECK THAT COLUMNS HAVE BEEN REMOVED FROM ANONYMISATION PROCESS."
        fi

        exit 99
    else
       echo
       ok "***********************************"
       ok "**  schema OK - no changes found **"
       ok "***********************************"
    fi
}

CONNECTION=
ANON_DATA_DIR=
ANON_DB=
NI_EXTRACT=false
DELETE_OLD_HISTORY=true
DUMP_OLCS_SCHEMA=false
SKIP_SCHEMA_CHECK=false
CHECK_SCHEMA_ONLY=false
ENABLE_DISABLE_INFILE_ON_SERVER=false
OLCS_SCHEMA_REFERENCE_FILE=$DEFAULT_OLCS_SCHEMA_REFERENCE_FILE

while getopts "c:d:f:S:NHhFDCI" opt; do
  case $opt in
    c)
        CONNECTION=$OPTARG ;;
    d)
        ANON_DB=$OPTARG ;;
    f)
        ANON_DATA_DIR=$OPTARG ;;
    N)
        NI_EXTRACT=true ;;
    H)
        DELETE_OLD_HISTORY=false ;;
    F)
        SKIP_SCHEMA_CHECK=true ;;
    S)
        OLCS_SCHEMA_REFERENCE_FILE=$OPTARG ;;
    D)
        log "Create new schema reference file $OLCS_SCHEMA_REFERENCE_FILE ..."
        dump_OLCS_schema $OLCS_SCHEMA_REFERENCE_FILE
        log "...done"
        exit;;
    C)
        CHECK_SCHEMA_ONLY=true ;;
    I)
        ENABLE_DISABLE_INFILE_ON_SERVER=true ;;
    h)
        usage ;;
    \?)
      usage "Invalid option: -$OPTARG";
      ;;
    :)
      usage "Option -$OPTARG requires an argument.";
      ;;
  esac
done

if $ENABLE_DISABLE_INFILE_ON_SERVER; then
    enable_local_infile_on_server
fi

if $SKIP_SCHEMA_CHECK; then
    echo
    warn "*********************************************"
    warn "** WARNING - schema check has been skipped **"
    warn "*********************************************"
else
    check_OLCS_schema

    if $CHECK_SCHEMA_ONLY; then
        exit
    fi
fi

if [ -z $ANON_DB ] || [ -z $ANON_DATA_DIR ]; then
  usage
fi

if [ ! -d $ANON_DATA_DIR ]; then
    log_error "$ANON_DATA_DIR directory does not exist!"
fi

if [ ! -r $OLCS_SCHEMA_REFERENCE_FILE ]; then
    log_error "$OLCS_SCHEMA_REFERENCE_FILE file does not exist!"
fi

if $NI_EXTRACT; then

    run_NI_anonymisation;

else
    run_anonymisation;
fi

if $ENABLE_DISABLE_INFILE_ON_SERVER; then
    disable_local_infile_on_server
fi

#!/bin/env bash

# NI Extract

HOME_DIR=$(pwd)

NI_EXTRACT_PROC="sp_NI_Extract"
CONTINUE_NI_EXTRACT=false
ANONYMISE_NI_EXTRACT_SWITCH="-N"
DISABLE_SCHEMA_CHECK="-F"
RUN_NI_ANONYMISATION=false
CREATE_NI_XML_DUMP=false;
CONNECTION=
DVA_FILE_PREFIX="dvacompliance-"
MANIFEST_FILE="${DVA_FILE_PREFIX}manifest.txt"

usage () {

    if [ -n "$1" ]; then
       echo ERROR : $1
    fi

    echo "usage: $(basename $0) -d <database> [OPTIONS]"
    echo "Options:"
    echo " -c <connection> - db connection string"
    echo " -A - run NI anonymisation"
    echo " -a <anon home dir> - anonymisation home directory"
    echo " -f <anon temp data file dir> - anonymisation temp data file directory"
    echo " -X <xml dump file dir> - create XML db dump in specified directory"
    echo " -C - continue current NI Extract"
    echo " -h - display usage"
    exit
}

log () {
    printf "\n%s %s\n" "$(date "+%Y-%m-%d %H:%M:%S")" "$1"
}

log_error () {
    log "$1"
    exit 1
}

clean_up ( ) {
    rm  $NI_EXTRACT_DUMP_DIR/$XML_DUMP_FILE $NI_EXTRACT_DUMP_DIR/$MANIFEST_FILE > /dev/null 2>&1
    rm -rf $ANON_DATA_DIR > /dev/null 2>&1
}

while getopts "c:d:a:f:X:CAh" opt; do
  case $opt in
    c)
        CONNECTION=$OPTARG ;;
    d)
        DB=$OPTARG ;;
    a)
        ANON_HOME=$OPTARG ;;
    f)
        ANON_DATA_DIR=$OPTARG ;;
    A)
        RUN_NI_ANONYMISATION=true ;;
    C)
        CONTINUE_NI_EXTRACT=true ;;
    X)
        NI_EXTRACT_DUMP_DIR=$OPTARG
        CREATE_NI_XML_DUMP=true
        ;;
    h)
        usage ;;
    \?)
        usage "Invalid option: -$OPTARG" ;;
    :)
        usage "Option -$OPTARG requires an argument." ;;
  esac
done

if [ -z $DB ] ; then
  usage
fi

if $RUN_NI_ANONYMISATION ; then
    if [ -z $ANON_HOME ] || [ -z $ANON_DATA_DIR ] ; then
       log "ANON_HOME and ANON_DATA_DIR are required to run NI Anonymisation"
       usage
    fi
fi

if $CREATE_NI_XML_DUMP ; then
    if [ -z $NI_EXTRACT_DUMP_DIR ] ; then
       log "NI_EXTRACT_DUMP_DIR is required for creating XML dump of NI db. "
       usage
    fi
fi


cd scripts

log "starting NI_Extract (Database: $DB)..."

if ! $CONTINUE_NI_EXTRACT ; then
    log "generate procedures..."
    ./generate_procedure_scripts.sh "$CONNECTION" $DB || log_error "generate_procedure_scripts.sh FAILED!"

    log "create database objects for NI Extract..."
    ./install_NI_Extract_db_objects.sh "$CONNECTION" $DB || log_error "install_NI_Extract_db_objects.sh FAILED!"

    log "run pre NI Extract procedures..."
    ./run-pre-NI-Extract.sh  "$CONNECTION" $DB || log_error "run-pre-NI-Extract.sh FAILED!"
fi

cd $HOME_DIR

log "calling $DB.$NI_EXTRACT_PROC($CONTINUE_NI_EXTRACT)..."
mysql -q $CONNECTION -e  "call $DB.$NI_EXTRACT_PROC($CONTINUE_NI_EXTRACT);" || log_error "$DB.$NI_EXTRACT_PROC($CONTINUE_NI_EXTRACT) FAILED!"

cd scripts

log "run post NI Extract..."
./run-post-NI-Extract.sh  "$CONNECTION" $DB || log_error "run-post-NI-Extract.sh FAILED!"

log "...NI_Extract completed OK"

if $RUN_NI_ANONYMISATION ; then

    log "Running NI Anonymisation..."

    cd $ANON_HOME

    if [ ! -d $ANON_DATA_DIR ]; then
        mkdir $ANON_DATA_DIR
    fi

    ./run_anonymisation.sh -c "$CONNECTION" -d $DB -f $ANON_DATA_DIR $ANONYMISE_NI_EXTRACT_SWITCH $DISABLE_SCHEMA_CHECK ||  log_error "NI Anonymisation FAILED!"

    log "...NI Anonymisation completed OK"
fi

cd $HOME_DIR

log "validate NI Extract..."
./run_validate_NI_Extract.sh "$CONNECTION" $DB || log_error "run_validate_NI_Extract.sh FAILED!"

cd scripts

log "drop NI Extract db objects..."
./uninstall_NI_Extract_db_objects.sh "$CONNECTION" $DB || log_error "uninstall_NI_Extract_db_objects.sh FAILED!"

if $CREATE_NI_XML_DUMP ; then

    FILE=${DVA_FILE_PREFIX}$(date "+%Y%m%d%H%M%S")
    XML_DUMP_FILE=${FILE}.xml
    TAR_FILE=${FILE}.tar.gz

    log "Creating NI XML dump file $NI_EXTRACT_DUMP_DIR/$XML_DUMP_FILE..."

    if [ ! -d $NI_EXTRACT_DUMP_DIR ]; then
        mkdir $NI_EXTRACT_DUMP_DIR
    fi

    cd $NI_EXTRACT_DUMP_DIR

    mysqldump $CONNECTION -X $DB --set-gtid-purged=OFF | tr -cd '\11\12\15\40-\176' > $XML_DUMP_FILE || log_error "create NI database dump FAILED!"

    log "Creating manifest file $NI_EXTRACT_DUMP_DIR/$MANIFEST_FILE..."

    sha256sum $XML_DUMP_FILE > $MANIFEST_FILE || log_error "create manifest file $NI_EXTRACT_DUMP_FILE_PATH/$MANIFEST_FILE FAILED!"

    log "Creating tar file $NI_EXTRACT_DUMP_DIR/$TAR_FILE..."

    tar -czf $TAR_FILE $XML_DUMP_FILE $MANIFEST_FILE || log_error "create tar file $NI_EXTRACT_DUMP_FILE_PATH/$TAR_FILE FAILED!"
     
    tar tvf $TAR_FILE

    log "...tar file created OK"
fi

clean_up

log "NI Extract All done!"

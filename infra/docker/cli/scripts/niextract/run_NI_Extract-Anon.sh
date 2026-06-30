#!/bin/env bash

# Run NI Extract with Anonymisation and create tar file with XML dump and manifest.

DB=$1
ANON_HOME=$2
ANON_TEMP_FILE_DIR=$3
NI_DUMP_DIR=$4
CONTINUE_RUN=$5

LOG_FILE="$(basename $0).log"

if [ -n "$CONTINUE_RUN" ]; then

    echo "re-starting NI Extract..."
    ./NI_Extract.sh -d $DB -A -a $ANON_HOME -f $ANON_TEMP_FILE_DIR -X $NI_DUMP_DIR -C > $LOG_FILE 2>&1
else

    echo "running NI Extract..."
    ./NI_Extract.sh -d $DB -A -a $ANON_HOME -f $ANON_TEMP_FILE_DIR -X $NI_DUMP_DIR > $LOG_FILE 2>&1
fi

if [ $? -ne 0 ]; then
    echo "...NI Extract completed with eror(s), please check $LOG_FILE for details."
else
    echo "...NI Extract completed OK."
fi


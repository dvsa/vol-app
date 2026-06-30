#!/bin/env bash

# Run NI Extract

DB=$1
CONTINUE_RUN=$2

LOG_FILE="$(basename $0).log"

if [ -n "$CONTINUE_RUN" ]; then

    echo "re-starting NI Extract..."
    ./NI_Extract.sh -d $DB -C  > $LOG_FILE 2>&1
else

    echo "running NI Extract..."
    ./NI_Extract.sh -d $DB > $LOG_FILE 2>&1
fi

if [ $? -ne 0 ]; then
    echo "...NI Extract completed with eror(s), please check $LOG_FILE for details."
else
    echo "...NI Extract completed OK."
fi

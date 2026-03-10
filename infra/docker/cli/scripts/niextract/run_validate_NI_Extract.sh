#!/bin/env bash

# Validate NI Extract

CONNECTION=$1
DB=$2

PROC="sp_validate_NI_Extract"

mysql $CONNECTION -e  "CALL $DB.$PROC;" | tr -d '/-/'


#! /bin/env bash

# Used for oLCS-16709 - generate csv output with two names in forename field.

NAMES_CSV_FILE=$1

foreNames=( $(awk -F"," '{ print $1 }' $NAMES_CSV_FILE) )
familyNames=( $(awk -F"," '{ print $2 }' $NAMES_CSV_FILE) )
multiNames='.*\s.*'

declare count int=0
while true ; do
 
foreName1=${foreNames[$((RANDOM%${#foreNames[@]}))]}
foreName2=${foreNames[$((RANDOM%${#foreNames[@]}))]}
familyName=${familyNames[$((RANDOM%${#familyNames[@]}))]}

if [ ${#foreName1} -gt 1 ] && [ ${#foreName2} -gt 1 ] && [ ${#familyName} -gt 1 ] && [ ${foreName1} != ${foreName2} ] && [[ ! ${familyName} =~ $multiNames ]]; then

    printf "%s %s,%s\n" $foreName1 $foreName2 $familyName

	(( count++ ))
    if [ $count -ge 5000 ]; then
        break
    fi
fi


done 

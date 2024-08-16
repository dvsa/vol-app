#!/usr/bin/env bash

# This is scripts tests that the data validators are working correctly on the backend
# It does this by setting up the data (mysql), calling a backend URL (curl) and asserting the response for a 200 or 400
# For issues see mat.evans@valtech.co.uk
#
# NB Currently run this against the dev test database (run inn Vagrant), It will need ID's changing if running on
# another database

curlOptions='-s -H "X-Pid: f1e9435a0975c1007d77bf466ac7507e66cc599ae3cafa3b2b0909cac2cc9da2" -H "Content-Type: application/json"'
backendUrl="http://olcs-backend/api"
mysqlOptions="olcs_be"
userId=611
userOrganisationId=1
userLicenceId=7
userApplicationId=7
notUserOrganisationId=30

while getopts "hf:" opt; do
  case $opt in
    h)
        echo;
        echo Run the API endpoint tests, default is to run tests in all *.tests.sh files
        echo;
        echo "  -h        Show help (this)"
        echo "  -f <file> Run a specific test file eg application.tests.sh"
        echo;
        exit;
      ;;
    f)
        file=$OPTARG
      ;;
  esac
done

testsPassed=0
testsFailed=0

# Assert an enpoint responds with a HTTP status code
#
# @param $1 Api endpoint URL
# @param $2 Expected HTTP response code
# @param $3 Method PUT, POST, DELETE. Default is GET
# @param $4 JSON data to include with POST, PUT
function assertHttpCode {

    url=$1
    expectedHttpCode=$2
    method=$3
    data=$4

    if [ -z $expectedHttpCode ]; then
        expectedHttpCode="200"
    fi

    if [ -z $method ]; then
        method="GET"
    fi


    if [ $method = "GET" ]; then
        cmd="curl $curlOptions -X $method --head $backendUrl/$url"
    else
        cmd="curl $curlOptions -X $method -d '$data' -D - $backendUrl/$url"
    fi
    result=$(eval $cmd)
    match=$(grep -o "HTTP/1.1 $expectedHttpCode" <<< $result)

    if [ -z "$match" ]; then
        ((testsFailed+=1))
        echo "FAILED assertHttpCode $expectedHttpCode $method $backendUrl/$url $data"
        echo " |- Received " $(grep -o 'HTTP/1.1 [a-zA-Z0-9]*' <<< $result)
        echo "$result"
    else
        ((testsPassed+=1))
        echo "PASSED assertHttpCode $expectedHttpCode $method $backendUrl/$url $data"
    fi
}

# Execute some SQL
# @param $1 SQL to execute
function executeSql {
    mysql $mysqlOptions -e "$1"
}


# if no file specified then run all test files
if [ -z "$file" ]; then
    for f in ./*.tests.sh; do
        echo
        echo "*** Running tests in $f ***"
        source $f
    done
else
    source $file
fi

echo
echo "$testsPassed Test(s) PASSED"
echo "$testsFailed Test(s) FAILED"
echo

if [ "$testsFailed" -ne "0" ]; then
    exit 1;
fi
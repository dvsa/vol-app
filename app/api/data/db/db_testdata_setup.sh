#!/bin/sh

# causes the shell to exit if any subcommand or pipeline returns a non-zero status.
set -e

ETL_PATH="../../../olcs-etl/"

if [ ! -d $ETL_PATH ]; then
  echo
  echo "Cannot find you ETL directory, You'll have to run it manually!!!"
  echo
  echo "Go to your ERL directory and then run"
  echo "./create-properties-from-mycnf.sh"
  echo "./create-base.sh"
  echo "liquibase/liquibase update -Ddataset=testdata"
  echo
  exit
fi

cd $ETL_PATH
./create-properties-from-mycnf.sh
./create-base.sh
liquibase/liquibase update -Ddataset=testdata

sudo service httpd restart

echo "All done!"

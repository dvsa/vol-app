#!/bin/bash
set -e

cat > /liquibase/liquibase.properties << EOF
driver=com.mysql.cj.jdbc.Driver
url=jdbc:mysql://${DB_HOST}:${DB_PORT}/${DB_NAME}
username=${DB_USER}
password=${DB_PASSWORD}
classpath=/liquibase/changelog/mysql-connector-java-8.0.21/mysql-connector-java-8.0.21.jar
changeLogFile=changesets/OLCS.xml
logLevel=info
liquibase.hub.mode=off
EOF

if [[ "$1" == "--dry-run" ]]; then
    echo "Running in dry-run mode - showing pending changes:"
    liquibase status --verbose
    liquibase update-sql
else
    echo "Running migrations..."
    liquibase update
fi

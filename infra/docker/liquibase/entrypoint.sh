#!/bin/bash
set -e

cd /liquibase/changelog

# Default to 'all' if ENVIRONMENT not set (maintains backward compatibility) e.g. local envs
CONTEXT=${ENVIRONMENT:-all}

echo "Liquibase running with context: ${CONTEXT}"

LIQUIBASE_OPTS="--driver=com.mysql.cj.jdbc.Driver \
  --classpath=/liquibase/changelog/mysql-connector-java-8.0.21/mysql-connector-java-8.0.21.jar \
  --url=jdbc:mysql://${DB_HOST}:${DB_PORT}/${DB_NAME} \
  --username=${DB_USER} \
  --password=${DB_PASSWORD} \
  --changelog-file=changesets/OLCS.xml \
  --log-level=info"

if [[ "$1" == "--dry-run" ]]; then
    echo "Running in dry-run mode - showing pending changes:"
    liquibase ${LIQUIBASE_OPTS} status --verbose --context-filter=${CONTEXT}
    liquibase ${LIQUIBASE_OPTS} update-sql --context-filter=${CONTEXT}
else
    echo "Running migrations..."
    liquibase ${LIQUIBASE_OPTS} update --context-filter=${CONTEXT}
fi

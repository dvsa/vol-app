#!/usr/bin/env bash
# Builds the liquibase image with a dummy changelog and checks the ETL sha is stamped in.
set -euo pipefail
cd "$(dirname "$0")"
tmp=$(mktemp -d); trap 'rm -rf "$tmp"' EXIT
mkdir -p "$tmp/changelog"; echo "<databaseChangeLog/>" > "$tmp/changelog/OLCS.xml"
cp Dockerfile entrypoint.sh "$tmp/"
docker build --build-arg ETL_SHA=0123456789abcdef0123456789abcdef01234567 -t liquibase-stamp-test "$tmp" >/dev/null
label=$(docker image inspect liquibase-stamp-test --format '{{index .Config.Labels "org.opencontainers.image.revision"}}')
[ "$label" = "0123456789abcdef0123456789abcdef01234567" ] || { echo "label missing: '$label'"; exit 1; }
id=$(docker create liquibase-stamp-test)
docker cp "$id:/liquibase/ETL_SHA" "$tmp/ETL_SHA"; docker rm "$id" >/dev/null
grep -q 0123456789abcdef0123456789abcdef01234567 "$tmp/ETL_SHA" || { echo "ETL_SHA file missing"; exit 1; }
echo "OK"

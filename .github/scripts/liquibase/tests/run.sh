#!/usr/bin/env bash
# Runs every tests/test_*.sh. Usage: bash .github/scripts/liquibase/tests/run.sh
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")"
source ./lib.sh
for f in test_*.sh; do
  [ -e "$f" ] || continue
  source "./$f"
done
for t in $(declare -F | awk '{print $3}' | grep '^test_' | sort); do
  run_test "$t"
done
echo "passed=${PASSED} failed=${FAILED}"
[ "$FAILED" -eq 0 ]

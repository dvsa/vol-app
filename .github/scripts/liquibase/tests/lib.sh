#!/usr/bin/env bash
# Minimal test helpers for the liquibase scripts. No external test framework.
set -euo pipefail

SCRIPTS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
FAKE_BIN="$(mktemp -d)"
export PATH="${FAKE_BIN}:${PATH}"
export FAKE_BIN
FAILED=0
PASSED=0

# fake_cmd <name> <body>: create an executable named <name> in FAKE_BIN whose contents are <body>.
fake_cmd() {
  printf '#!/usr/bin/env bash\n%s\n' "$2" > "${FAKE_BIN}/$1"
  chmod +x "${FAKE_BIN}/$1"
}

assert_eq() { # <expected> <actual> <label>
  if [ "$1" = "$2" ]; then return 0; fi
  echo "    expected: $1"; echo "    actual:   $2"; return 1
}

assert_contains() { # <needle> <haystack> <label>
  if grep -qF -- "$1" <<<"$2"; then return 0; fi
  echo "    missing: $1"; echo "    in: $2"; return 1
}

# assert_fails <cmd...>: succeeds only if the command exits non-zero.
assert_fails() { if "$@" >/dev/null 2>&1; then echo "    expected failure but succeeded: $*"; return 1; fi; }

run_test() { # <function name>
  if "$1"; then PASSED=$((PASSED+1)); echo "PASS $1"; else FAILED=$((FAILED+1)); echo "FAIL $1"; fi
}

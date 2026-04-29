#!/usr/bin/env bash
# Drop into each PHP olcs-* library (olcs-auth, olcs-common, olcs-elasticsearch,
# olcs-etl, olcs-logging, olcs-transfer, olcs-utils, olcs-xmltools) and reference
# from the README as a one-time post-clone step:
#
#     ./create-git-hooks.sh
#
# Installs the git-secrets pre-commit / commit-msg / prepare-commit-msg hooks
# and registers the DVSA-standard AWS pattern set. Idempotent.
#
# Mirrors the pattern used by dvsa/theory-test-admission-service:create-git-hooks.sh.

set -euo pipefail

if ! command -v git-secrets >/dev/null 2>&1; then
  cat <<'MSG' >&2
git-secrets is not installed.
  macOS:  brew install git-secrets
  Other:  https://github.com/awslabs/git-secrets#installing-git-secrets
MSG
  exit 1
fi

git secrets --install -f
git secrets --register-aws

echo "git-secrets hooks installed and AWS patterns registered."

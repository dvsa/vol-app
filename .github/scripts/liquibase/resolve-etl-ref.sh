#!/usr/bin/env bash
# Resolve an olcs-etl ref (branch, tag or commit sha) to a commit sha.
# Usage: resolve-etl-ref.sh <ref>
# Env:   GH_TOKEN (for gh), ETL_REPO (default dvsa/olcs-etl)
# Out:   etl_sha=<40 hex> and etl_short=<12 hex>, one per line (GITHUB_OUTPUT format)
set -euo pipefail

ref="${1:?usage: $0 <ref>}"
repo="${ETL_REPO:-dvsa/olcs-etl}"

if ! sha=$(gh api "repos/${repo}/commits/${ref}" --jq '.sha' 2>/dev/null); then
  echo "::error::Cannot resolve olcs-etl ref '${ref}' in ${repo}" >&2
  exit 1
fi
if ! [[ "$sha" =~ ^[0-9a-f]{40}$ ]]; then
  echo "::error::Unexpected commit sha '${sha}' for ref '${ref}'" >&2
  exit 1
fi
echo "etl_sha=${sha}"
echo "etl_short=${sha:0:12}"

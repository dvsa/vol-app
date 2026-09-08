#!/usr/bin/env bash
# Create a lightweight tag in olcs-etl at a given commit, idempotently.
# Usage: create-etl-tag.sh <tag> <sha>
# Env:   GH_TOKEN (needs contents:write on the repo), ETL_REPO (default dvsa/olcs-etl)
set -euo pipefail

tag="${1:?usage: $0 <tag> <sha>}"
sha="${2:?usage: $0 <tag> <sha>}"
repo="${ETL_REPO:-dvsa/olcs-etl}"

existing=$(gh api "repos/${repo}/git/ref/tags/${tag}" --jq '.object.sha' 2>/dev/null || true)
if [ -n "$existing" ]; then
  if [ "$existing" = "$sha" ]; then
    echo "tag ${tag} already at ${sha}"
    exit 0
  fi
  echo "::error::tag ${tag} already exists at ${existing}, expected ${sha}. Refusing to move it." >&2
  exit 1
fi

gh api -X POST "repos/${repo}/git/refs" -f ref="refs/tags/${tag}" -f sha="${sha}" >/dev/null
echo "created tag ${tag} at ${sha}"

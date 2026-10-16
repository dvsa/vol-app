#!/usr/bin/env bash
# Create a lightweight tag in olcs-etl at a given commit, idempotently.
# Usage: create-etl-tag.sh <tag> <sha>
# Env:   GH_TOKEN (needs contents:write on the repo), ETL_REPO (default dvsa/olcs-etl)
set -euo pipefail

tag="${1:?usage: $0 <tag> <sha>}"
sha="${2:?usage: $0 <tag> <sha>}"
repo="${ETL_REPO:-dvsa/olcs-etl}"

# When a request fails, gh api prints the JSON error body on stdout and "gh: ... (HTTP nnn)"
# on stderr, so the exit status and stderr decide what happened, never the stdout text.
lookup_err=$(mktemp)
if existing=$(gh api "repos/${repo}/git/ref/tags/${tag}" --jq '[.object.type, .object.sha] | join(" ")' 2>"$lookup_err"); then
  found=true
else
  found=false
  if ! grep -q 'HTTP 404' "$lookup_err"; then
    echo "::error::Could not look up tag ${tag} in ${repo}: $(cat "$lookup_err")" >&2
    rm -f "$lookup_err"
    exit 1
  fi
fi
rm -f "$lookup_err"

if [ "$found" = true ]; then
  existing_type="${existing%% *}"
  existing_sha="${existing#* }"
  if [ "$existing_type" != "commit" ]; then
    echo "::error::tag ${tag} in ${repo} is an annotated tag object (${existing_sha}), expected a lightweight tag at ${sha}. Check it by hand." >&2
    exit 1
  fi
  if [ "$existing_sha" = "$sha" ]; then
    echo "tag ${tag} already at ${sha}"
    exit 0
  fi
  echo "::error::tag ${tag} already exists at ${existing_sha}, expected ${sha}. Refusing to move it." >&2
  exit 1
fi

gh api -X POST "repos/${repo}/git/refs" -f ref="refs/tags/${tag}" -f sha="${sha}" >/dev/null
echo "created tag ${tag} at ${sha}"

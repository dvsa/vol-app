#!/usr/bin/env bash
# Summarise a liquibase Batch job into the GitHub step summary and fail on migration errors.
# Usage: summarise-batch-job.sh <job_id> <etl_sha> <image_digest> [previous_image_tag]
# Env:   BATCH_LOG_GROUP (required), ENVIRONMENT, DRY_RUN, GITHUB_STEP_SUMMARY
set -euo pipefail

job_id="${1:?usage: $0 <job_id> <etl_sha> <image_digest> [previous_image_tag]}"
etl_sha="${2:?usage: $0 <job_id> <etl_sha> <image_digest> [previous_image_tag]}"
digest="${3:-unknown}"
previous="${4:-}"
group="${BATCH_LOG_GROUP:?BATCH_LOG_GROUP must be set}"
summary="${GITHUB_STEP_SUMMARY:-/dev/stdout}"
etl_short="${etl_sha:0:12}"

status=$(aws batch describe-jobs --jobs "$job_id" --query 'jobs[0].status' --output text)

# Batch keeps one log stream per attempt; read them all so a retried job is summarised completely.
mapfile -t streams < <(aws batch describe-jobs --jobs "$job_id" \
  --query 'jobs[0].attempts[].container.logStreamName' --output text | tr '\t' '\n' | grep -v -E '^(None)?$' || true)
if [ "${#streams[@]}" -eq 0 ]; then
  mapfile -t streams < <(aws batch describe-jobs --jobs "$job_id" \
    --query 'jobs[0].container.logStreamName' --output text | grep -v -E '^(None)?$' || true)
fi

log=""
for stream in "${streams[@]}"; do
  log+=$(aws logs get-log-events --log-group-name "$group" --log-stream-name "$stream" \
    --start-from-head --query 'events[].message' --output text | tr '\t' '\n')
  log+=$'\n'
done

run_count=$(grep -E '^Run:' <<<"$log" | tail -1 | awk '{print $2}' || true)

# "Running Changeset" lines whose next line is a precondition skip ("NOT applying") are not real work.
executed=$(awk '
  /^Running Changeset:/ { pending=$0; next }
  pending != "" { if ($0 !~ /NOT applying/) print pending; pending="" }
  END { if (pending != "") print pending }' <<<"$log" | sed -E 's/^Running Changeset: /- /')

{
  echo "### Database migrations (${ENVIRONMENT:-unknown})"
  echo
  echo "- Batch job: \`${job_id}\` finished \`${status}\`"
  echo "- olcs-etl commit: \`${etl_sha}\`"
  echo "- Image digest: \`${digest}\`"
  echo
  echo '```'
  grep -E '^(Run|Previously run|Filtered out|Total change sets|Context mismatch):' <<<"$log" | tail -5 || true
  echo '```'
  echo
  echo "Changesets executed:"
  if [ -n "$executed" ]; then echo "$executed"; else echo "- none"; fi
  echo
} >> "$summary"

if grep -qE 'Migration failed|Unexpected error running Liquibase' <<<"$log"; then
  echo "::error::Liquibase reported a migration failure in job ${job_id}" >&2
  exit 1
fi
if [ "${DRY_RUN:-false}" != "true" ] && ! grep -qF "Liquibase command 'update' was executed successfully" <<<"$log"; then
  echo "::error::Liquibase update did not report success in job ${job_id} (status ${status})" >&2
  exit 1
fi
if [ "${run_count:-0}" = "0" ] && [ -n "$previous" ] && [ "$previous" != "$etl_short" ]; then
  echo "::warning::olcs-etl image changed from ${previous} to ${etl_short} but no changesets ran; check the changelog actually changed"
fi

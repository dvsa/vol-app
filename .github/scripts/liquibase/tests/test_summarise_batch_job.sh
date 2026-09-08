#!/usr/bin/env bash

# Fake aws: describe-jobs returns SUCCEEDED and one attempt stream; get-log-events returns the fixture
# as tab-separated messages, which is what --output text produces for events[].message.
_fake_aws_for_logs() { # <fixture file> [status]
  export FAKE_LOG_FILE="$1" FAKE_JOB_STATUS="${2:-SUCCEEDED}"
  fake_cmd aws '
    case "$*" in
      *describe-jobs*jobs\[0\].status*) echo "$FAKE_JOB_STATUS" ;;
      *describe-jobs*attempts*) echo "liquibase/default/abc" ;;
      *describe-jobs*container.logStreamName*) echo "liquibase/default/abc" ;;
      *get-log-events*) paste -sd "\t" "$FAKE_LOG_FILE" ;;
    esac'
  export BATCH_LOG_GROUP=/aws/batch/vol-app-reg-liquibase ENVIRONMENT=reg
  export GITHUB_STEP_SUMMARY="${FAKE_BIN}/summary.md"; : > "$GITHUB_STEP_SUMMARY"
  unset DRY_RUN
}

test_summary_lists_real_changesets_only() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-run3.log"
  bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-1 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc >/dev/null
  s=$(cat "$GITHUB_STEP_SUMMARY")
  assert_contains "sp_elastic_person.sql" "$s" &&
  assert_contains "Run:                          3" "$s" &&
  ! grep -q "testdataset.xml" <<<"$s"
}

test_failed_migration_exits_nonzero() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-failed.log" FAILED
  assert_fails bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-2 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc
}

test_succeeded_status_without_success_line_exits_nonzero() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-failed.log" SUCCEEDED
  assert_fails bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-3 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc
}

test_run0_with_changed_image_warns() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-run0.log"
  out=$(bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-4 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc 334a8f85ab12 2>&1)
  assert_contains "::warning::" "$out"
}

test_run0_with_same_image_does_not_warn() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-run0.log"
  out=$(bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-5 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc 5b4f0336947d 2>&1)
  ! grep -q "::warning::" <<<"$out"
}

test_dry_run_does_not_require_update_success_line() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-dryrun.log" SUCCEEDED
  export DRY_RUN=true
  bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-6 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc >/dev/null
}

test_dry_run_still_fails_on_migration_error() {
  _fake_aws_for_logs "${SCRIPTS_DIR}/tests/fixtures/liquibase-failed.log" SUCCEEDED
  export DRY_RUN=true
  assert_fails bash "${SCRIPTS_DIR}/summarise-batch-job.sh" job-7 5b4f0336947de34e14b01ea674d5bf528b7c3166 sha256:abc
}

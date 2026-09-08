#!/usr/bin/env bash

test_resolve_branch_returns_sha_and_short() {
  fake_cmd gh 'echo "5b4f0336947de34e14b01ea674d5bf528b7c3166"'
  out=$(bash "${SCRIPTS_DIR}/resolve-etl-ref.sh" main)
  assert_contains "etl_sha=5b4f0336947de34e14b01ea674d5bf528b7c3166" "$out" &&
  assert_contains "etl_short=5b4f0336947d" "$out"
}

test_resolve_calls_commits_endpoint_with_ref() {
  fake_cmd gh 'echo "$@" > "${FAKE_BIN}/gh.args"; echo "0123456789abcdef0123456789abcdef01234567"'
  bash "${SCRIPTS_DIR}/resolve-etl-ref.sh" fix/my-branch >/dev/null
  assert_contains "repos/dvsa/olcs-etl/commits/fix/my-branch" "$(cat "${FAKE_BIN}/gh.args")"
}

test_resolve_unknown_ref_fails() {
  fake_cmd gh 'exit 1'
  assert_fails bash "${SCRIPTS_DIR}/resolve-etl-ref.sh" does-not-exist
}

test_resolve_rejects_non_sha_output() {
  fake_cmd gh 'echo "not-a-sha"'
  assert_fails bash "${SCRIPTS_DIR}/resolve-etl-ref.sh" main
}

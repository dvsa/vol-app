#!/usr/bin/env bash

test_create_tag_when_absent_posts_ref() {
  fake_cmd gh '
    echo "$@" >> "${FAKE_BIN}/gh.calls"
    case "$*" in
      *git/ref/tags/*) echo "{\"message\":\"Not Found\",\"status\":\"404\"}"; echo "gh: Not Found (HTTP 404)" >&2; exit 1 ;;
      *-X\ POST*git/refs*) echo "{}" ;;
    esac'
  rm -f "${FAKE_BIN}/gh.calls"
  bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 >/dev/null
  calls=$(cat "${FAKE_BIN}/gh.calls")
  assert_contains "ref=refs/tags/release-v9.9.9" "$calls" &&
  assert_contains "sha=5b4f0336947de34e14b01ea674d5bf528b7c3166" "$calls"
}

test_existing_tag_at_same_sha_is_noop() {
  fake_cmd gh '
    echo "$@" >> "${FAKE_BIN}/gh.calls"
    case "$*" in *git/ref/tags/*) echo "commit 5b4f0336947de34e14b01ea674d5bf528b7c3166" ;; esac'
  rm -f "${FAKE_BIN}/gh.calls"
  bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 >/dev/null
  ! grep -q "POST" "${FAKE_BIN}/gh.calls"
}

test_existing_tag_at_other_sha_fails_with_error() {
  fake_cmd gh 'case "$*" in *git/ref/tags/*) echo "commit 0000000000000000000000000000000000000000" ;; esac'
  err=$(bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 2>&1 >/dev/null || true)
  assert_contains "::error::" "$err" &&
  assert_fails bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166
}

test_lookup_failure_other_than_404_fails_without_posting() {
  fake_cmd gh '
    echo "$@" >> "${FAKE_BIN}/gh.calls"
    case "$*" in *git/ref/tags/*) echo "{\"message\":\"Bad credentials\"}"; echo "gh: Bad credentials (HTTP 401)" >&2; exit 1 ;; esac'
  rm -f "${FAKE_BIN}/gh.calls"
  err=$(bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 2>&1 >/dev/null || true)
  assert_contains "::error::" "$err" &&
  ! grep -q "POST" "${FAKE_BIN}/gh.calls"
}

test_annotated_tag_is_rejected() {
  fake_cmd gh 'case "$*" in *git/ref/tags/*) echo "tag 1111111111111111111111111111111111111111" ;; esac'
  err=$(bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 2>&1 >/dev/null || true)
  assert_contains "annotated" "$err" &&
  assert_fails bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166
}

#!/usr/bin/env bash

test_create_tag_when_absent_posts_ref() {
  fake_cmd gh '
    echo "$@" >> "${FAKE_BIN}/gh.calls"
    case "$*" in
      *git/ref/tags/*) exit 1 ;;               # lookup: not found
      *-X\ POST*git/refs*) echo "{}" ;;        # create
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
    case "$*" in *git/ref/tags/*) echo "5b4f0336947de34e14b01ea674d5bf528b7c3166" ;; esac'
  rm -f "${FAKE_BIN}/gh.calls"
  bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166 >/dev/null
  ! grep -q "POST" "${FAKE_BIN}/gh.calls"
}

test_existing_tag_at_other_sha_fails() {
  fake_cmd gh 'case "$*" in *git/ref/tags/*) echo "0000000000000000000000000000000000000000" ;; esac'
  assert_fails bash "${SCRIPTS_DIR}/create-etl-tag.sh" release-v9.9.9 5b4f0336947de34e14b01ea674d5bf528b7c3166
}

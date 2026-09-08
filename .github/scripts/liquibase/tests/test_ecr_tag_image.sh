#!/usr/bin/env bash

# A fake aws that answers describe-images/batch-get-image/put-image from environment variables
# and records every put-image call.
_fake_aws_for_tagging() {
  export FAKE_FIXTURE="${SCRIPTS_DIR}/tests/fixtures/batch-get-image.json"
  fake_cmd aws '
    args="$*"
    case "$args" in
      *batch-get-image*) cat "$FAKE_FIXTURE" ;;
      *describe-images*imageTag=src*) echo "sha256:aaa" ;;
      *describe-images*imageTag=already*) echo "sha256:aaa" ;;
      *describe-images*imageTag=stale*) echo "sha256:bbb" ;;
      *describe-images*) exit 254 ;;
      *put-image*) echo "$args" >> "${FAKE_BIN}/put.log" ;;
    esac'
  rm -f "${FAKE_BIN}/put.log"
}

test_tags_missing_and_stale_but_skips_matching() {
  _fake_aws_for_tagging
  out=$(bash "${SCRIPTS_DIR}/ecr-tag-image.sh" vol-app/liquibase src newtag already stale)
  puts=$(cat "${FAKE_BIN}/put.log")
  assert_contains "image-tag newtag" "$puts" &&
  assert_contains "image-tag stale" "$puts" &&
  ! grep -q "image-tag already" <<<"$puts" &&
  assert_contains "image_digest=sha256:aaa" "$out"
}

test_passes_media_type_to_put_image() {
  _fake_aws_for_tagging
  bash "${SCRIPTS_DIR}/ecr-tag-image.sh" vol-app/liquibase src newtag >/dev/null
  assert_contains "image-manifest-media-type application/vnd.oci.image.manifest.v1+json" "$(cat "${FAKE_BIN}/put.log")"
}

test_missing_source_tag_fails() {
  fake_cmd aws 'echo "{\"images\":[],\"failures\":[{\"failureCode\":\"ImageNotFound\"}]}"'
  assert_fails bash "${SCRIPTS_DIR}/ecr-tag-image.sh" vol-app/liquibase nosuch newtag
}

test_no_extra_tags_still_prints_digest() {
  _fake_aws_for_tagging
  out=$(bash "${SCRIPTS_DIR}/ecr-tag-image.sh" vol-app/liquibase src)
  assert_contains "image_digest=sha256:aaa" "$out" &&
  [ ! -e "${FAKE_BIN}/put.log" ]
}

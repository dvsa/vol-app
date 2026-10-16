#!/usr/bin/env bash
# Apply one or more tags to the image already tagged <source_tag> in an ECR repository.
# Idempotent: tags already pointing at the same digest are left alone.
# Usage: ecr-tag-image.sh <repository> <source_tag> <tag>...
# Out:   one line per tag, then image_digest=sha256:...
set -euo pipefail

repo="${1:?usage: $0 <repository> <source_tag> <tag>...}"
src="${2:?usage: $0 <repository> <source_tag> <tag>...}"
shift 2

resp=$(aws ecr batch-get-image --repository-name "$repo" --image-ids imageTag="$src" --output json)
manifest=$(jq -r '.images[0].imageManifest // empty' <<<"$resp")
media_type=$(jq -r '.images[0].imageManifestMediaType // empty' <<<"$resp")
if [ -z "$manifest" ]; then
  echo "::error::No image tagged '${src}' in ${repo}" >&2
  exit 1
fi
digest=$(aws ecr describe-images --repository-name "$repo" --image-ids imageTag="$src" \
  --query 'imageDetails[0].imageDigest' --output text)

for tag in "$@"; do
  current=$(aws ecr describe-images --repository-name "$repo" --image-ids imageTag="$tag" \
    --query 'imageDetails[0].imageDigest' --output text 2>/dev/null || true)
  if [ "$current" = "$digest" ]; then
    echo "${repo}:${tag} already at ${digest}"
    continue
  fi
  aws ecr put-image --repository-name "$repo" --image-tag "$tag" \
    --image-manifest "$manifest" --image-manifest-media-type "$media_type" >/dev/null
  echo "${repo}:${tag} -> ${digest}"
done
echo "image_digest=${digest}"

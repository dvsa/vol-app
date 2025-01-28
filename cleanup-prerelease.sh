#!/bin/bash

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [[ "$CURRENT_BRANCH" == "prerelease" ]]; then
    echo "Cannot delete prerelease while on prerelease branch"
    echo "Please checkout another branch first"
    exit 1
fi

if ! git show-ref --verify --quiet refs/heads/prerelease; then
    echo "No local prerelease branch found"
    exit 0
fi

git branch -D prerelease

if git ls-remote --exit-code --heads origin prerelease >/dev/null 2>&1; then
    git push origin --delete prerelease
    echo "Remote prerelease branch deleted"
fi

echo "Prerelease branch cleanup complete"
#!/bin/bash

VERSION=$1
SOURCE_BRANCH=$(git rev-parse --abbrev-ref HEAD)

if [ -z "$VERSION" ]; then
    echo "Error: Version argument required"
    echo "Usage: ./create-prerelease.sh <version>"
    echo "Example: ./create-prerelease.sh 1.1.0"
    echo ""
    echo "This will create a prerelease branch from your current branch: $SOURCE_BRANCH"
    exit 1
fi

echo "Updating current branch ($SOURCE_BRANCH)..."
git pull origin $SOURCE_BRANCH || exit 1

echo "Creating prerelease branch from $SOURCE_BRANCH..."
git checkout -b prerelease || exit 1

echo "Copying changelog..."
if [ -f "CHANGELOG.md" ]; then
    cp CHANGELOG.md CHANGELOG-PRERELEASE.md || exit 1
    git add CHANGELOG-PRERELEASE.md
    echo "Committing changelog..."
    git commit -m "chore: initialize prerelease changelog from $SOURCE_BRANCH" || exit 1
else
    echo "Warning: No CHANGELOG.md found in $SOURCE_BRANCH"
    touch CHANGELOG-PRERELEASE.md
    git add CHANGELOG-PRERELEASE.md
    git commit -m "chore: initialize empty prerelease changelog" || exit 1
fi

echo "Creating release commit..."
git commit --allow-empty -m "chore: release v${VERSION}-rc1
Release-As: v${VERSION}-rc1" || exit 1

echo "Pushing to remote..."
git push origin prerelease || exit 1

echo "Prerelease branch setup complete!"
echo "A release-please PR for v${VERSION}-rc1 should be created shortly."
echo "Source branch was: $SOURCE_BRANCH"

# VOL-APP Pre-Release Process

## Overview

This document outlines the process for creating and managing pre-releases of VOL-APP, allowing for thorough testing before formal releases.
The process described will generally only be used by the VOL APP dev team, and is used purely for conducting QA up to and including `prep`, when non-prod testing alone cannot provide sufficient confidence in the release.

## Why Use a Pre-Release?

Sometimes changes are large or risky or need the PROD acct.

-   Work needs final testing or acceptance in prep (prod account) with the data/resources only available in that environment.
-   Confidence is lower that all issues have been caught in dev/int, and we want to avoid multiple releases and version bumps.
-   For changes such as the Laminas 3 migration, or other changes which will likely yield lots of bugs throughout testing cycle.

## Process Steps

### 1. Creating a Pre-Release Branch

1. Start from either:
    - The main branch for standard releases
    - A feature branch requiring specific testing
2. Create the pre-release branch using the helper script:
    ```bash
    ./create-prerelease.sh <version>
    # Example: ./create-prerelease.sh 1.1.0
    ```
    This will:
    - Create the `prerelease` branch
    - Copy CHANGELOG.md to CHANGELOG-PRERELEASE.md
    - Create the Release-As commit for the RC1 version

### 2. Managing the Pre-Release

Before merging the Release Please pre-release PR, tag olcs-etl manually with the correct tag for tne pre-release version.

When Release-Please creates the PR:

1. Review the PR contents
2. Merge the PR to create version `v<version>-rc1` (e.g., v1.1.0-rc1)
3. This triggers the CD pipeline which will:
    - Deploy through dev, int, and prep environments
    - Run all automated tests
    - Stop before production - ready for manual acceptance testing on prep

### 3. Making Changes During Testing

If issues are found during testing:

1. Create a fix branch from prerelease:
    ```bash
    git checkout prerelease
    git checkout -b fix/issue-description
    ```
2. Make changes using conventional commits
3. Create and merge a PR back to prerelease
4. Release-Please will automatically create a PR for the next RC version (e.g., rc2)
5. Merge the Release-Please PR to deploy the new RC version

### 4. Pre-Release Sign-Off/Approval

Testing a pre-release is complete when:

1. All automated tests have passed
2. Stakeholders sign-off on their manual acceptance testing

### 5. Synchronising with Main

If no issues arise during the testing of the first release-candidate (e.g., `v1.1.0-rc1`), and the code on `prerelease` is effectively identical to `main` except for version/changelog commits:

1. Confirm that **no additional commits** were made to `main` after you created the `prerelease` branch.
2. Verify via `git diff main prerelease` (or a GitHub comparison) that the only differences are those Release-Please made to create the RC version.
3. If there are **no other changes**, then no sync PR is needed—simply merge the existing Release-Please PR for the final version (e.g., `v1.1.0`) into `main`, which will trigger the normal release pipeline.

If any changes have been required, the pre-release branch must be merged into main.

1. Create a PR to merge prerelease into main
2. Use a conventional commit message summarising all changes:

    ```
    feat: merge tested prerelease features

    Merges pre-release changes tested in v1.1.0-rc1 through rc3:
    - Change 1
    - Change 2
    ```

3. Review the PR carefully to ensure:
    - All needed changes are included
    - No unintended changes are present, if they are more testing may be required

### 6. Proceed to full Release Process

The main branch is now ready for the full release process, documented in the [VOL-APP Release Process](release-process.md).

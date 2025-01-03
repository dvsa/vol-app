# Workflow for Pre-Releases with Release-Please

## Summary

This RFC proposes implementing a pre-release workflow using release-please, allowing the VOL team to create and manage pre-release versions of the application without interfering with the main release cycle. The proposal includes workflow configurations, helper scripts, and documented procedures based on successful testing in the [dvsa-release-please-poc](https://github.com/dvsa/dvsa-release-please-poc) repository.

## Motivation

The current CD pipeline only promotes release-please releases beyond the nonprod account. There is often a need to have pre-release code promoted up to the prod account (PREP env) for testing and access to certain resources. This workflow allows the creation of pre-releases which can be promoted as desired, without interfering with normal version numbering or consuming unreleased commit messages on main.

## Detailed Design

### Branching Strategy

Pre-releases will be handled on a dedicated branch named `prerelease`. This branch can be created from either:

-   The main branch
-   Any feature branch requiring pre-release testing

### Version Management

-   Pre-release versions (e.g., -rc1) must be explicitly specified using the Release-As annotation
-   Example commit message:

```bash
git commit --allow-empty -m "chore: release v1.2.3-rc1
Release-As: v1.2.3-rc1"
```

-   This is simplified with a helper script (below)

### Changelog Management

-   Main branch maintains CHANGELOG.md
-   Prerelease branch maintains a separate CHANGELOG-PRERELEASE.md
-   When creating a prerelease branch, the main changelog must be copied to CHANGELOG-PRERELEASE.md (see helper script)
-   Changes that happen on prerelease remain isolated from main's changelog, `main`'s changelog will not loose any commits that go out in a pre-release for testing.

### GitHub Actions Workflow

The workflow will:

-   Watch for pushes to both main and prerelease branches
-   Apply appropriate configuration based on the branch
-   Create releases and update changelogs accordingly
-   Generate correct source archives for both main and prerelease versions

```yaml
on:
    push:
        branches:
            - main
            - prerelease

jobs:
    release-please:
        name: Release
        runs-on: ubuntu-latest
        permissions:
            contents: write
            pull-requests: write
        outputs:
            tag_name: ${{ steps.release.outputs.tag_name }}
            release_created: ${{ steps.release.outputs.release_created }}
        steps:
            - name: Checkout Repository
              uses: actions/checkout@v3
              with:
                  ref: ${{ github.ref_name }}
                  fetch-depth: 0

            - name: Configure Release-Please for Branch
              run: |
                  if [[ "${{ github.ref_name }}" == "prerelease" ]]; then
                    echo "Configuring for pre-releases..."
                    cp prerelease-config.json release-please-config.json
                  fi

            - name: Run Release-Please
              id: release
              uses: googleapis/release-please-action@v4
              with:
                  target-branch: ${{ github.ref_name }}

            - name: Cleanup
              if: always()
              run: |
                  if [[ "${{ github.ref_name }}" == "prerelease" ]]; then
                    git checkout -- release-please-config.json || true
                  fi
```

### Release Creation

-   Main branch releases follow standard semver (1.0.0, 1.1.0, etc.)
-   Prerelease versions use RC suffix (1.1.0-rc1, 1.1.0-rc2, etc.)
-   Prerelease versions can be marked as drafts to control visibility in the releases list

## Implementation Plan

1. ### Add Required Configuration Files:

    - release-please-config.json for main branch (existing)
    - prerelease-config.json for prerelease branch (new)
    - .release-please-manifest.json for version tracking (existing)

2. ### Update GitHub Actions Workflow:

    - Modify existing cd.yaml to handle both branches
    - Add configuration switching logic (as shown in yaml above)
    - Ensure CD yaml pipeline does not allow prereleases to move beyond PREP

3. ### Copy & Test Helper Scripts:

    - Import and adapt scripts from dvsa-release-please-poc:
    - [create-prerelease.sh](https://github.com/dvsa/dvsa-release-please-poc/blob/main/create-prerelease.sh) for branch creation / changelog copy and Release-As commit.
    - [cleanup-prerelease.sh](https://github.com/dvsa/dvsa-release-please-poc/blob/main/cleanup-prerelease.sh) for branch cleanup / deletion when pre-release testing is complete.

4. ### Documentation:
    - Add workflow documentation to the repository
    - Include examples and common scenarios
    - Document helper script usage

## Example Workflow

1. ### Development Phase:

    - Complete work on your feature/fix branch
    - Ensure all tests pass locally
    - Decide this code needs testing via a pre-release

2. ### Create Pre-release:

```bash
git checkout your-feature-branch
./create-prerelease.sh 1.1.0
```

This creates a prerelease branch and triggers release-please to create a PR for v1.1.0-rc1.

3. ### Merge The Release-Please PR to Create v1.1.0-rc1 and trigger the CD pipeline to deploy your code:

    - Merge the release-please PR to create v1.1.0-rc1
    - This triggers the CD pipeline to deploy your code
    - The pre-release is deployed through DEV/INT/PREP environments
    - Automated tests run as normal

4. ### Iterative Testing & Fixes:

    - As testing proceeds and issues are identified:
        ```bash
        git checkout prerelease
        git checkout -b fix/issue-description
        ```
    - Make changes using conventional commits
    - Create a PR to merge back into prerelease
    - After PR review and merge, release-please will create a PR for v1.1.0-rc2
    - Merge the release-please PR to create the next RC version
    - Each RC version follows the same deployment pipeline

5. ### Final Integration:
    - Once testing is complete and the pre-release is approved
    - Create a PR to merge prerelease into main
    - Use a conventional commit message summarizing all changes:

        ```bash
        feat: merge tested prerelease features

        Merges and promotes pre-release changes tested in v1.1.0-rc1 through rc3:
        - Added new validation logic
        - Fixed date formatting
        - Enhanced error handling
        ```

    - After merging to main, clean up:
        ```bash
        ./cleanup-prerelease.sh
        ```

This workflow allows for iterative testing of changes through the environments while maintaining clear versioning and change history.

## Reference Implementation

A working proof of concept has been implemented in the [dvsa-release-please-poc](https://github.com/dvsa/dvsa-release-please-poc) repository, demonstrating:

-   Configuration files
-   GitHub Actions workflow
-   Helper scripts
-   Example releases and pre-releases

## Next Steps

1. Review and approve RFC
2. Port configurations and workflows from PoC to vol-app
3. Implement helper scripts
4. Update documentation
5. Train team on new workflow

## Questions and Considerations

-   When should pre-releases be used vs regular releases?
-   How long should pre-release branches live?
-   What naming conventions for RC versions?
-   Who has authority to create/merge pre-releases?

## Appendix

-   [Git Branching Diagram for proposed workflow](https://github.com/dvsa/dvsa-release-please-poc/blob/main/prerelease-branching-diagram.png)
-   [Flowchart for proposed workflow](https://github.com/dvsa/dvsa-release-please-poc/blob/main/prerelease-workflow-chart.png)

### Helper Scripts & Config files

Two shell scripts to help manage the pre-release process are included in the PoC repository. These will need to be copied to vol-app and tested.
Same for the example release-please config file for prerelease branch.

# Workflow for Pre-Releases with Release-Please

## Summary

This RFC proposes a branching strategy and workflow configuration to support pre-releases in a repository using `release-please`. Pre-releases will be managed via a dedicated `prerelease` branch, and version numbers for pre-releases will be explicitly controlled using the `Release-As` commit message annotation. Changes to the `cd.yaml` workflow are required to dynamically apply branch-specific configurations.

## Motivation

The new CD pipeline will only promote release-please releases beyond the nonprod account. It may be desireable to have pre-release code promoted up to the prod account (PREP env) for testing/access to certain resources etc. This proposed workflow allows the creation of pre-releases which can be promoted as far as desired, that will not interfere with normal version numbering, and will not consume the unreleased commit messages on `main` for subsequent real releases.

---

## General Approach to Pre-Releases

### **Branching Strategy**

-   Pre-releases will be handled on a dedicated branch named `prerelease`.
-   To initiate a pre-release cycle:

    1. Branch off `main`:
        ```bash
        git checkout main
        git pull origin main
        git checkout -b prerelease
        ```
    2. Specify the pre-release version explicitly in a commit:

        ```bash
        git commit --allow-empty -m "chore: release vX.Y.Z-rc1

        Release-As: vX.Y.Z-rc1"
        ```

    3. Push the branch to the remote repository:
        ```bash
        git push origin prerelease
        ```

### **Version Management with `Release-As`**

-   Pre-release identifiers (e.g., `-rc`, `-alpha`, `-beta`) are **not automatically appended by `release-please`**.
-   The version must be explicitly specified using the `Release-As` annotation in a commit message. Examples:

    -   For an initial release candidate:

        ```bash
        git commit --allow-empty -m "chore: release v1.2.3-rc1

        Release-As: v1.2.3-rc1"
        ```

    -   For an alpha release:

        ```bash
        git commit --allow-empty -m "chore: release v1.2.3-alpha.1

        Release-As: v1.2.3-alpha.1"
        ```

-   If no `Release-As` annotation is provided, `release-please` may default to stable versioning logic, potentially skipping the desired pre-release identifier.

---

## Proposed Changes to `cd.yaml`

### **Current Workflow**

The existing workflow assumes a single configuration file (`release-please-config.yaml`) for all releases and does not differentiate between `main` and `prerelease` branches.

### **Updated Workflow**

The workflow will dynamically apply the appropriate configuration based on the branch. Two configuration files will be introduced:

1. `release-please-config.json` for stable releases.
2. `prerelease-config.json` for pre-releases.

#### **Updated `cd.yaml`**

```yaml
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
            # Step 1: Checkout the repository
            - name: Checkout Repository
              uses: actions/checkout@v3

            # Step 2: Configure release-please for the branch
            - name: Configure Release-Please for Branch
              run: |
                  if [[ "${{ github.ref_name }}" == "prerelease" ]]; then
                    echo "Configuring for pre-releases..."
                    cp prerelease-config.json release-please-config.json
                  fi

            # Step 3: Run the release-please action
            - name: Run Release-Please
              id: release
              uses: googleapis/release-please-action@v4
              with:
                  target-branch: ${{ github.ref_name }}
```

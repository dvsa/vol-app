# VOL-APP Release Documentation

This document explains the release process for the VOL-APP application.

### Prerequisites

1. All required changes (including any from olcs-etl) are already merged into main.
2. A Release-Please Pull Request (PR) is open for the next version (e.g., v5.20.1).

### Standard Release Steps

1. Merge the pending Release-Please PR into main.

    - This compiles conventional commit messages into the changelog and triggers a new semver release (e.g. v5.20.1).
    - The CD pipeline will detect the “release” context and proceed in release mode.

2. CD Pipeline Deploys to Nonprod

    - Builds, tests, and deploys to dev, then int. Smoke tests run in dev.
    - Smoke tests and regression tests run in int.

3. Deployment to prep (Production Account)

    - Pipeline then deploys to the pre-production (prep) environment.
    - Further smoke tests are run in prep.

4. Manual Approval for Production

    - Pipeline pauses before deploying to prod.
    - An authorised reviewer approves in GitHub, then the pipeline proceeds.
    - Terraform, migrations, and final deployment to prod happen. No automated tests run on prod.

5. Release Completed
    - The final semver release (v5.20.1) is now live.
    - Future changes will form a new release.

---

## Roles and Responsibilities

### vol-app developers / platform engineers

-   Modify app code, Terraform, Dockerfiles, etc., to add features or fix bugs
-   Merge the Release-Please PRs for standard releases

### vol-app QAs

-   Manage and monitor automated tests run by the pipeline
-   Perform additional manual testing in the relevant environments

### DVSA staff

-   Perform UAT in `prep`
-   Prepare acceptance reports or any relevant validation

### TSS staff

-   Verify that testing and UAT are complete
-   Approve progression to `prod` as authorised reviewers, if satisfied

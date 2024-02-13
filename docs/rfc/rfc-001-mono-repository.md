# RFC-001: Mono-repository

## Summary

Merge the three VOL repositories into a single repository and relocate the Docker compose file into the repository.

## Problem

The VOL project consists of three separate repositories: [olcs-backend](http://github.com/dvsa/olcs-backend), [olcs-selfserve](http://github.com/dvsa/olcs-selfserve), and [olcs-internal](http://github.com/dvsa/olcs-internal), resulting in a total of 11 repositories for the VOL application. Many of these repositories share similar infrastructure and contain duplicate code, such as linting configurations and GitHub Action workflows. Managing continuous deployment across separate repositories poses complexity, requiring sequential releases and manual intervention for rollbacks.

## Proposal

Create a mono-repository by merging the following repositories:

-   [olcs-backend](http://github.com/dvsa/olcs-backend)
-   [olcs-selfserve](http://github.com/dvsa/olcs-selfserve)
-   [olcs-internal](http://github.com/dvsa/olcs-internal)
-   [vol-docker-compose](http://github.com/dvsa/vol-docker-compose)

Subdirectories within the mono-repository will be named without their prefixes, i.e., `api`, `selfserve`, `internal`, and `vol-docker-compose` will be replaced.

### Local Environment

A Docker compose file within the repository will facilitate provisioning of a local environment using `docker compose up -d`, simplifying the setup process.

### Duplication

Duplication of workflows and static analysis tools will be eliminated by consolidating them at the root level of the directory. A top-level `composer.json` will contain scripts to execute tools across all directories. Specific-project tools can still be run by navigating to the respective sub-project directory.

### Granular Workflows

GitHub Action workflows will be designed to execute only on sub-projects with detected changes during CI/CD, optimizing pipeline efficiency.

## Implementation

-   Create a new repository named `vol-app`.
-   Adopt a directory structure similar to the following:
    ```
    |__ `.github`
    |       |__ `workflows/`
    |            |__ `ci.yaml`
    |            |__ `cd.yaml`
    |__ `app`
            |__ `api/`
            |__ `internal/`
            |__ `selfserve/`
    |__ `infra` (optional)
            |__ `terraform/`
    |__ `vendor-bin/`
            |__ `phpcs`
            |__ `phpstan`
            |__ `psalm`
    |__ `.editorconfig`
    |__ `docker-compose.yaml`
    |__ ...
    ```
-   Import repositories while retaining their git history for relevant commits during the merge.

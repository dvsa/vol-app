---
sidebar_position: 50
---

# CI/CD

At a high level, the VOL application's CI/CD pipeline can be visualised as follows:

![CI/CD](./assets/ci-cd.png)

## Continuous Integration (CI)

:::note

Only parts of the application that have changed are updated during continuous integration.

:::

The CI workflow is triggered by a `pull_request` to the default branch (`main`). The workflow is responsible for building, testing the application & infrastructure, and running Terraform plans on the infrastructure.

**Workflow**: [.github/workflows/ci.yaml](https://github.com/dvsa/vol-app/blob/main/.github/workflows/ci.yaml).

Various tools run on CI to ensure the quality of the codebase:

### ![](./assets/languages/php.svg) PHP

#### Testing

-   [PHPUnit](https://github.com/sebastianbergmann/phpunit)

#### Linting

-   [PHPStan](https://github.com/phpstan/phpstan)
-   [Psalm](https://github.com/vimeo/psalm)
-   [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

#### Security

-   [Snyk](https://snyk.io/)

### ![](./assets/languages/docker.svg) Docker

#### Linting

-   [Hadolint](https://github.com/hadolint/hadolint)

#### Testing

-   Docker build (`docker build`)

#### Security

-   [Trivy](https://github.com/aquasecurity/trivy)

### ![](./assets/languages/terraform.svg) Terraform

#### Linting

-   [TFLint](https://github.com/terraform-linters/tflint)
-   Terraform format (`terraform fmt`)

#### Testing

-   Terraform validate (`terraform validate`)
-   Terraform plan (`terraform plan`)

#### Security

-   [Trivy](https://github.com/aquasecurity/trivy)

## Continuous Deployment (CD)

The CD workflow is triggered by a successful merge to the default branch (`main`). The workflow is responsible for building and deploying the application through to the production environment.

:::tip

To view the current version of the application in each environment, refer to the [deployments](https://github.com/dvsa/vol-app/deployments) page.

:::

**Workflow**: [.github/workflows/cd.yaml](https://github.com/dvsa/vol-app/blob/main/.github/workflows/cd.yaml).

![CD workflow](./assets/cd.png)

### Path to production

```mermaid
---
config:
    flowchart:
        htmlLabels: false
---
graph LR
    start["`Merge to **main**`"] --> dev_account

    subgraph dev_account["`**Development Account**`"]
        direction TB
        dev["`Deploy to **Development**`"]:::success ==> dev_e2e{E2E Tests}
        dev_e2e ===>|"`**Pass**`"| int[Integration]:::success
        dev_e2e ==>|"`**Fail**`"| dev_stop["`Stop`"]:::negative

        int["`Deploy to **Integration**`"] ==> int_e2e{E2E Tests}
        int_e2e ===>|"`**Pass**`"| int_release[Complete]:::success
        int_e2e ==>|"`**Fail**`"| int_rollback[Rollback]:::negative
    end

    dev_account ---> is-release{"Is Release?"} --->|"`**Yes**`"| prod_account
    is-release -->|"`**No**`"| release_stop["`Stop`"]:::negative

    subgraph prod_account["`**Production Account**`"]
        direction TB
        prep["`Deploy to **Pre-production**`"]:::success ==> prep_e2e{E2E Tests}
        prep_e2e ===>|"`**Pass**`"| prod[Production]:::success
        prep_e2e ==>|"`**Fail**`"| prep_rollback[Rollback]:::negative

        prod["`Deploy to **Production**`"] ==> prod_e2e{E2E Tests}
        prod_e2e ===>|"`**Pass**`"| prod_deploy[Complete]:::success
        prod_e2e ==>|"`**Fail**`"| prod_rollback[Rollback]:::negative
    end

    classDef success fill:#C5E1A5,color:#000,stroke:#388e3c
    classDef negative fill:#f7d3d3,color:#000,stroke:#c62828
```

### Environments

The VOL application has four environments: Development, Integration, Pre-production, and Production. Each environment has a specific purpose and stability level.

:::warning

The stability is only as good as the test coverage that assures it. For example, integration tests are only as good as the E2E tests that assure that it's working.

:::

| Environment    | Abbr. | Purpose                                                        | Stability | Test coverage provided by     |
| -------------- | ----- | -------------------------------------------------------------- | --------- | ----------------------------- |
| Development    | DEV   | Used by the team to test their changes.                        | Unstable  | Continuous Integration (CI)   |
| Integration    | INT   | A stable environment assured by the E2E tests.                 | Stable    | End-to-End (E2E) tests        |
| Pre-production | PREP  | Used to test the application in a production-like environment. | Stable    | Release tests                 |
| Production     | PROD  | The live environment.                                          | Stable    | User Acceptance Testing (UAT) |

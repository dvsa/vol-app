---
sidebar_position: 10
---

# Initial Terraform setup in an AWS account

This runbook is for setting up Terraform that builds the `vol-app` in an AWS account for the first time.

## What you'll need before you start

-   The new AWS account ID.
-   Currently authenticated with a user/role with the `AdministratorAccess` managed policy.

## Steps

1. Change into the [`infra/terraform/accounts/_init`](https://github.com/dvsa/vol-app/tree/main/infra/terraform/accounts/_init) directory.

    :::warning

    Ensure that the local Terraform state is cleared from any previous runs before running the following commands.

    :::

1. Run `terraform init`.

1. Run `terraform apply`.

1. Create a directory in [`infra/terraform/accounts`](https://github.com/dvsa/vol-app/tree/main/infra/terraform/accounts) named the new account name.

    :::note

    Replace `[ACCOUNT_ID]` with the AWS account ID.

    :::

    1. Create a `main.tf`:

        ```hcl
        locals {
            environments = []
        }

        module "environment-remote-state" {
            for_each = toset(local.environments)

            source = "../../modules/remote-state"

            identifier = "vol-app"

            environment = each.key

            # Environments will re-use the same bucket as the account.
            create_bucket = false
        }

        module "account" {
            source = "../../modules/account"

            create_ecr_resources    = true
            create_assets_bucket    = true
            create_github_resources = true

            github_oidc_subjects = concat(
                [
                    "dvsa/vol-app:ref:refs/heads/main",         # `.github/workflows/docker.yaml` & `.github/workflows/assets.yaml`.
                    "dvsa/vol-app:environment:account-nonprod", # `.github/workflows/deploy-account.yaml`.
                ],
                [
                    for env in local.environments : "dvsa/vol-app:environment:${env}" # `.github/workflows/deploy-environment.yaml`
                ],
            )

            github_oidc_readonly_subjects = [
                for env in local.environments : "dvsa/vol-app:pull_request"
            ]

            github_oidc_readonly_role_policies = merge(
                {
                    DynamodbStateLock = "arn:aws:iam::[ACCOUNT_ID]:policy/vol-app-[ACCOUNT_ID]-terraform-state-lock-policy",
                    S3StateLock       = "arn:aws:iam::[ACCOUNT_ID]:policy/vol-app-[ACCOUNT_ID]-terraform-state-policy"
                },
                { for env, remote-state in module.environment-remote-state : "${title(env)}DynamodbStateLock" => remote-state.dynamodb_state_lock_policy_arn }
            )
        }
        ```

    1. Create a `provider.tf`:

        ```hcl
        terraform {
            required_providers {
                aws = {
                    source  = "hashicorp/aws"
                    version = "~> 5.49.0"
                }
            }

            required_version = ">= 1.0"
        }

        provider "aws" {
            region = "eu-west-1"

            allowed_account_ids = [ACCOUNT_ID]
        }
        ```

    1. Create a `backend.tf`:

        ```hcl
        terraform {
            backend "s3" {
                bucket         = "vol-app-[ACCOUNT_ID]-terraform-state"
                dynamodb_table = "vol-app-[ACCOUNT_ID]-terraform-state-lock"
                encrypt        = true
                key            = "account.tfstate"
                region         = "eu-west-1"
            }
        }
        ```

1. Run `terraform init`.

1. Run `terraform apply`.

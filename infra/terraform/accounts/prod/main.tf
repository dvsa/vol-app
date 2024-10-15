locals {
  environments = ["prep", "prod"]
}

# Imported as this provider has been created by the `vol-terraform` repository.
import {
  to = module.account.module.github[0].module.iam_github_oidc_provider[0].aws_iam_openid_connect_provider.this[0]
  id = "arn:aws:iam::146997448015:oidc-provider/token.actions.githubusercontent.com"
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
      DynamodbStateLock = "arn:aws:iam::146997448015:policy/vol-app-146997448015-terraform-state-lock-policy",
      S3StateLock       = "arn:aws:iam::146997448015:policy/vol-app-146997448015-terraform-state-policy"
    },
    { for env, remote-state in module.environment-remote-state : "${title(env)}DynamodbStateLock" => remote-state.dynamodb_state_lock_policy_arn }
  )
}
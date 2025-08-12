locals {
  environments = ["dev", "int"]

  # Domain configuration for this account
  domain_name        = "dev-dvsacloud.uk"
  maintenance_domain = "maintenance.dev-dvsacloud.uk"
}

# Imported as this provider has been created by the `vol-terraform` repository.
import {
  to = module.account.module.github[0].module.iam_github_oidc_provider[0].aws_iam_openid_connect_provider.this[0]
  id = "arn:aws:iam::054614622558:oidc-provider/token.actions.githubusercontent.com"
}

module "environment-remote-state" {
  for_each = toset(local.environments)

  source = "../../modules/remote-state"

  identifier = "vol-app"

  environment = each.key

  # Environments will re-use the same bucket as the account.
  create_bucket = false
}

# Route53 zone for maintenance page
data "aws_route53_zone" "public" {
  name = local.domain_name
}

module "account" {
  source = "../../modules/account"

  create_ecr_resources    = true
  create_assets_bucket    = true
  create_github_resources = true

  github_oidc_subjects = concat(
    [
      "dvsa/vol-app:ref:refs/heads/main", # `.github/workflows/docker.yaml` & `.github/workflows/assets.yaml`.
      "dvsa/vol-app:ref:refs/heads/prerelease",
      "dvsa/vol-app:environment:account-nonprod",
      "dvsa/vol-app:pull_request", # `.github/workflows/deploy-account.yaml`.
    ],
    [
      for env in local.environments : "dvsa/vol-app:environment:${env}" # `.github/workflows/deploy-environment.yaml`
    ],
  )

  github_oidc_readonly_subjects = concat(
    [
      "dvsa/vol-app:ref:refs/heads/main",
      "dvsa/vol-app:ref:refs/heads/prerelease",
      "dvsa/vol-app:pull_request",
      "dvsa/vol-app:environment:account-nonprod",
      "dvsa/vol-app:environment:dev",
      "dvsa/vol-app:environment:int",
    ]
  )

  github_oidc_readonly_role_policies = merge(
    {
      DynamodbStateLock = "arn:aws:iam::054614622558:policy/vol-app-054614622558-terraform-state-lock-policy",
      S3StateLock       = "arn:aws:iam::054614622558:policy/vol-app-054614622558-terraform-state-policy"
    },
    { for env, remote-state in module.environment-remote-state : "${title(env)}DynamodbStateLock" => remote-state.dynamodb_state_lock_policy_arn }
  )
}

# Account-level maintenance page
module "maintenance_page" {
  source = "../../modules/maintenance-page"

  maintenance_domain = local.maintenance_domain
  route53_zone_id    = data.aws_route53_zone.public.zone_id

  github_oidc_subjects = [
    "repo:dvsa/vol-app:environment:account-nonprod",
    "repo:dvsa/vol-app-maintenance:ref:refs/heads/main"
  ]

  providers = {
    aws.acm = aws.acm
  }
}

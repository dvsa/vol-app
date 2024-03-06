locals {
  environments = []
}

module "account" {
  source = "../../modules/account"
}

# Imported as this provider has been created by the `vol-terraform` repository.
import {
  to = module.account.module.github[0].module.iam_github_oidc_provider[0].aws_iam_openid_connect_provider.this[0]
  id = "arn:aws:iam::054614622558:oidc-provider/token.actions.githubusercontent.com"
}

module "remote-state" {
  for_each = toset(local.environments)

  source = "../../modules/remote-state"

  identifier = "vol-app"

  environment = each.key

  # Environments will re-use the same bucket as the account.
  create_bucket = false
}

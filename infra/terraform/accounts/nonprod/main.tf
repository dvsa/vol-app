locals {
  environments = ["dev", "int"]
}

module "account" {
  source = "../../modules/account"
}

module "remote-state" {
  for_each = toset(local.environments)

  source = "../../modules/remote-state"

  identifier = "vol-app"

  environment = each.key

  # Environments will re-use the same bucket as the account.
  create_bucket = false
}

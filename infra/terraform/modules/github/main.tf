locals {
  oidc_role_name          = var.oidc_role_prefix != null ? "${var.oidc_role_prefix}-github-actions-role" : "github-actions-role"
  oidc_readonly_role_name = var.oidc_role_prefix != null ? "${var.oidc_role_prefix}-github-actions-readonly-role" : "github-actions-readonly-role"
}

module "iam_github_oidc_provider" {
  count = var.create_oidc_provider ? 1 : 0

  source = "git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-provider?ref=55514b7873c411040395e024a422684998da77c2"
}

module "iam_github_oidc_role" {
  count = var.create_oidc_role ? 1 : 0

  source = "git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-role?ref=55514b7873c411040395e024a422684998da77c2"
  name   = local.oidc_role_name

  subjects                 = var.oidc_subjects
  permissions_boundary_arn = var.oidc_role_permissions_boundary_arn

  policies = merge(var.oidc_role_policies, {
    AdministratorAccess = "arn:aws:iam::aws:policy/AdministratorAccess",
  })
}

module "iam_github_oidc_readonly_role" {
  count = var.create_oidc_readonly_role ? 1 : 0

  source = "git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-role?ref=55514b7873c411040395e024a422684998da77c2"
  name   = local.oidc_readonly_role_name

  subjects                 = var.oidc_readonly_subjects
  permissions_boundary_arn = var.oidc_role_permissions_boundary_arn

  policies = merge(var.oidc_readonly_role_policies, {
    ReadOnlyAccess = "arn:aws:iam::aws:policy/ReadOnlyAccess",
  })
}

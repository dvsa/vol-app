locals {
  pull_request_subjects = [for subject in var.subjects : "${subject}:pull_request"]

  push_event_subjects = [for subject in var.subjects : "${subject}::ref:refs/heads/main"]
}

module "iam_github_oidc_provider" {
  count = var.create_oidc_provider ? 1 : 0

  source  = "terraform-aws-modules/iam/aws//modules/iam-github-oidc-provider"
  version = "~> 5.24"
}

module "iam_github_oidc_role" {
  count = var.create_oidc_role ? 1 : 0

  source  = "terraform-aws-modules/iam/aws//modules/iam-github-oidc-role"
  version = "~> 5.24"

  name = "github-actions-role"

  subjects                 = var.push_event_subjects
  permissions_boundary_arn = var.oidc_role_permissions_boundary_arn

  policies = merge(var.oidc_role_policies, {
    AdministratorAccess = "arn:aws:iam::aws:policy/AdministratorAccess",
  })
}

module "iam_github_oidc_readonly_role" {
  count = var.create_oidc_readonly_role ? 1 : 0

  source  = "terraform-aws-modules/iam/aws//modules/iam-github-oidc-role"
  version = "~> 5.24"

  name = "github-actions-readonly-role"

  subjects                 = var.pull_request_subjects
  permissions_boundary_arn = var.oidc_role_permissions_boundary_arn

  policies = merge(var.oidc_role_policies, {
    ReadOnlyAccess = "arn:aws:iam::aws:policy/ReadOnlyAccess",
  })
}

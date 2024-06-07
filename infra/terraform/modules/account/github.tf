module "github" {
  count = var.create_github_resources ? 1 : 0

  source = "../../modules/github"

  oidc_role_prefix = "vol-app"

  create_oidc_provider      = true
  create_oidc_role          = true
  create_oidc_readonly_role = true

  oidc_role_policies          = var.github_oidc_role_policies
  oidc_readonly_role_policies = var.github_oidc_readonly_role_policies

  oidc_subjects = var.github_oidc_subjects

  oidc_readonly_subjects = var.github_oidc_readonly_subjects
}

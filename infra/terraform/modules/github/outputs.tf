output "oidc_role_arn" {
  description = "The ARN of the GitHub OIDC role"
  value       = try(module.iam_github_oidc_role[0].arn, null)
}

output "oidc_readonly_role_arn" {
  description = "The ARN of the GitHub Readonly OIDC role"
  value       = try(module.iam_github_oidc_readonly_role[0].arn, null)
}

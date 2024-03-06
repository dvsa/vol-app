output "oidc_role_arn" {
  description = "The ARN of the GitHub OIDC role"
  value       = try(module.iam_github_oidc_role[0].arn, null)
}

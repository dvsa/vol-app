variable "create_github_resources" {
  type        = bool
  description = "Whether to create the GitHub resources."
  default     = true
}

variable "github_oidc_role_policies" {
  type        = map(string)
  description = "A map of policy names to policy ARNs to attach to the OIDC role."
  default     = {}
}

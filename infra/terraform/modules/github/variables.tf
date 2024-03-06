variable "repositories" {
  type        = list(string)
  description = "The list of repositories to add to the OIDC role."
}

variable "create_oidc_provider" {
  type        = bool
  description = "Whether to create an OIDC provider."
  default     = true
}

variable "create_oidc_role" {
  type        = bool
  description = "Whether to create an OIDC role."
  default     = true
}

variable "create_oidc_readonly_role" {
  type        = bool
  description = "Whether to create a readonly OIDC role. This is useful for pull requests."
  default     = true
}

variable "oidc_role_policies" {
  type        = map(string)
  description = "The map of policies to attach to the OIDC role."
  default     = {}
}

variable "oidc_readonly_role_policies" {
  type        = map(string)
  description = "The map of policies to attach to the OIDC readonly role."
  default     = {}
}

variable "oidc_role_permissions_boundary_arn" {
  type        = string
  description = "The ARN of the permissions boundary to use for the role."
  default     = null
}

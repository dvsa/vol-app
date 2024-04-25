variable "github_oidc_subjects" {
  type        = list(string)
  description = "The list of GitHub subjects to allow in the OIDC role."
  default     = []
}

variable "github_oidc_readonly_subjects" {
  type        = list(string)
  description = "The list of GitHub subjects to allow in the OIDC readonly role."
  default     = []
}

variable "create_github_resources" {
  type        = bool
  description = "Whether to create the GitHub resources."
  default     = false
}

variable "github_oidc_role_policies" {
  type        = map(string)
  description = "A map of policy names to policy ARNs to attach to the OIDC role."
  default     = {}
}

variable "github_oidc_readonly_role_policies" {
  type        = map(string)
  description = "The map of policies to attach to the OIDC readonly role."
  default     = {}
}

variable "ecr_read_access_arns" {
  type        = list(string)
  description = "The list of ARNs to attach to the ECR read role."
  default     = []
}

variable "ecr_read_write_access_arns" {
  type        = list(string)
  description = "The list of ARNs to attach to the ECR read-write role."
  default     = []
}

variable "create_ecr_resources" {
  type        = bool
  description = "Whether to create the ECR resources."
  default     = false
}

variable "create_assets_bucket" {
  type        = bool
  description = "Whether to create the assets bucket."
  default     = false
}

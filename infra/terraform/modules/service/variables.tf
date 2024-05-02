variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "domain_name" {
  type        = string
  description = "The domain name for the environment"
}

variable "assets_version" {
  type        = string
  description = "The version of the assets"
}

variable "services" {
  type = map(object({
    image              = string
    cpu                = number
    memory             = number
    security_group_ids = list(string)
    subnet_ids         = list(string)
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
  }))
  description = "The services to deploy"
  default     = {}
}

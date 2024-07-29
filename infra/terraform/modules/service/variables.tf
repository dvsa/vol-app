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

variable "vpc_id" {
  type        = string
  description = "The VPC ID"
}

variable "services" {
  type = map(object({
    version    = string
    repository = string
    cpu        = number
    memory     = number
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
    add_cdn_url_to_env        = optional(bool, false)
    lb_listener_arn           = string
    listener_rule_priority    = optional(number, 10)
    listener_rule_host_header = optional(string, "*")
    security_group_ids        = list(string)
    subnet_ids                = list(string)
    vpc_id                    = optional(string, null)
  }))
  description = "The services to deploy"
  default     = {}
}

variable "batch" {
  description = "Configuration for the batch process"
  type = object({
    version    = string
    repository = string
    subnet_ids = list(string)
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
    jobs = list(object({
      name     = string
      commands = list(string)
      cpu      = optional(number, 1)
      memory   = optional(number, 2048)
      timeout  = optional(number, 300)
    }))
  })
}

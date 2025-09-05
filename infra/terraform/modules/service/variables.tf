variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "legacy_environment" {
  type        = string
  description = "The legacy environment to deploy use"
}

variable "domain_env" {
  type        = string
  description = "The domain environment to deploy use"
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

variable "elasticache_url" {
  type        = string
  description = "The URL of the Elasticache cluster"
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
    add_cdn_url_to_env          = optional(bool, false)
    enable_autoscaling_policies = optional(bool, true)
    lb_arn                      = optional(string)
    lb_listener_arn             = optional(string)
    iuweb_pub_listener_arn      = optional(string)
    // The reason for this was to enable the parallel running of ECS and EC2 services.
    // This boolean will control the flow of traffic. If `true`, traffic will go to ECS. If `false`, traffic will go to EC2.
    // Can be removed when EC2 services are removed.
    listener_rule_enable              = optional(bool, true)
    listener_rule_priority            = optional(number, 10)
    listener_rule_host_header         = optional(list(string), ["*"])
    listener_rule_host_header_proving = optional(list(string), ["*"])
    security_group_ids                = list(string)
    subnet_ids                        = list(string)
    vpc_id                            = optional(string, null)
    host_port                         = optional(number, 8080)
    container_port                    = optional(number, 8080)
  }))
  description = "The services to deploy"
  default     = {}
}

variable "batch" {
  description = "Configuration for the batch process"
  type = object({
    cli_version          = string
    cli_repository       = string
    liquibase_repository = string
    api_secret_file      = string
    subnet_ids           = list(string)
    alert_emails         = optional(list(string))
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
    jobs = list(object({
      name     = string
      type     = optional(string, "default")
      queue    = optional(string, "default")
      commands = optional(list(string))
      cpu      = optional(number, 1)
      memory   = optional(number, 2048)
      timeout  = optional(number, 300)
      schedule = optional(list(string), [])
    }))
  })
}

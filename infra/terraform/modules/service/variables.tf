variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "services" {
  type = map(object({
    image              = string
    cpu                = number
    memory             = number
    security_group_ids = list(string)
    subnet_ids         = list(string)
  }))
  description = "The services to deploy"
  default     = {}
}

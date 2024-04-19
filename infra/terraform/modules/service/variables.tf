variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "vpc_ids" {
  type        = string
  description = "The environment to deploy to"
}

variable "vpc_azs" {
  type        = list(string)
  description = "The environment to deploy to"
}

variable "services" {
  type = map(object({
    image              = string
    cpu                = number
    memory             = number
    security_group_ids = list(string)
    subnet_ids         = list(string)
    cidr_blocks        = list(string)
  }))
  description = "The services to deploy"
  default     = {}
}

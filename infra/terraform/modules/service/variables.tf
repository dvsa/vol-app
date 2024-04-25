variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "vpc_ids" {
  type        = string
  description = "The VPC to deploy to"
}

variable "vpc_azs" {
  type        = list(string)
  description = "The VPC AZ to deploy to"
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
    access_point       = string
    security_group_ids = list(string)
    subnet_ids         = list(string)
    cidr_blocks        = list(string)
  }))
  description = "The services to deploy"
  default     = {}
}

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

variable "services" {
  type = map(object({
    image              = string
    efs_id             = string
    cpu                = number
    memory             = number
    security_group_ids = list(string)
    subnet_ids         = list(string)
    cidr_blocks        = list(string)
  }))
  description = "The services to deploy"
  default     = {}
}

variable "access_points" {
  type = map(object({
    root_directory = object({
      path = string
      creation_info = object({
        owner_gid   = number
        owner_uid   = number
        permissions = string
      })
    })
  }))
  description = "The efs access point configuration"
  default     = {}
}
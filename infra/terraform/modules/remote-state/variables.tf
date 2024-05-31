variable "identifier" {
  type        = string
  description = "The identifier of the resources. This is used to create a unique name for the resources."
}

variable "environment" {
  type        = string
  description = "The environment in which the resources are deployed. This is used to create a unique name for the resources."
  default     = null
}

variable "create_bucket" {
  type        = bool
  description = "Whether to create a state bucket or not."
  default     = true
}

variable "create_bucket_policy" {
  type        = bool
  description = "Whether to create a policy for the S3 bucket or not."
  default     = true
}

variable "create_dynamodb_policy" {
  type        = bool
  description = "Whether to create a policy for the DynamoDB table or not."
  default     = true
}

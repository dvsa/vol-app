variable "assets_version" {
  type        = string
  description = "The version of the assets"
}

variable "api_image_tag" {
  type        = string
  description = "The tag of the API image to deploy"
}

variable "selfserve_image_tag" {
  type        = string
  description = "The tag of the selfserve image to deploy"
}

variable "internal_image_tag" {
  type        = string
  description = "The tag of the internal image to deploy"
}

variable "cli_image_tag" {
  type        = string
  description = "The tag of the cli image to deploy"
}
variable "idp_bda_project_arn" {
  type        = string
  description = "ARN of the Bedrock Data Automation project for the IDP extraction pipeline. Created outside Terraform (e.g. via CDK POC or manually via the AWS console)."
}

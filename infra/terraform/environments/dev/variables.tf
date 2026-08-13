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

variable "idp_bedrock_prompt_version_arn" {
  type        = string
  description = "ARN of the immutable Bedrock managed prompt version to use for IDP AI analysis. Obtain from the Bedrock console or AWS CLI after deploying the prompt."
}

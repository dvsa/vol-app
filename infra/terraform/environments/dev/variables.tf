variable "assets_version" {
  type        = string
  description = "The version of the assets"
}

variable "api_image_tag" {
  type        = string
  description = "The tag of the API image to deploy"
  default     = "latest"
}

variable "selfserve_image_tag" {
  type        = string
  description = "The tag of the selfserve image to deploy"
  default     = "latest"
}

variable "internal_image_tag" {
  type        = string
  description = "The tag of the internal image to deploy"
  default     = "latest"
}



variable "environment" {
  type        = string
  description = "Deployment environment (dev, int, prep, prod)"
}

variable "bedrock_region" {
  type        = string
  description = "AWS region for Bedrock API calls"
  default     = "eu-west-1"
}

variable "bedrock_region_prefix" {
  type        = string
  description = "Cross-region inference profile prefix, e.g. 'eu' or 'us'"
  default     = "eu"
}

variable "classification_model_id" {
  type        = string
  description = "Bedrock foundation model ID for document classification. Combined with bedrock_region_prefix to form the cross-region inference profile ID."
  default     = "anthropic.claude-haiku-4-5-20251001-v1:0"
}

variable "lambda_timeout" {
  type        = number
  description = "Timeout in seconds for the classify-document Lambda"
  default     = 60
}

variable "lambda_memory_size" {
  type        = number
  description = "Memory in MB for the classify-document Lambda. 512 MB gives headroom for base64-encoding a multi-MB PDF in memory."
  default     = 512
}

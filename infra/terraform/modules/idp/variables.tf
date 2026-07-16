variable "environment" {
  type        = string
  description = "Deployment environment (dev, int, prep, prod)"
}

variable "documents_bucket_name" {
  type        = string
  description = "Name of the pre-existing S3 bucket that receives document uploads (e.g. the sabredav bucket managed in vol-terraform)."
}

variable "documents_key_prefix" {
  type        = string
  description = "S3 key prefix used as the EventBridge filter. Only Object Created events whose key starts with this prefix will start the Classification SM. The SM itself further narrows to the current year/month dynamically at runtime."
  default     = "migration/olcs/documents/Application/Financial_Evidence_Digital/"
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

# ============================================================
# Extraction SM — Bedrock Data Automation (BDA) configuration
# ============================================================

variable "bda_project_arn" {
  type        = string
  description = "ARN of the Bedrock Data Automation project used for bank statement extraction."
}

variable "bda_project_stage" {
  type        = string
  description = "BDA project stage to invoke. LIVE uses the latest published blueprint version."
  default     = "LIVE"
}

# ============================================================
# Extraction SM — Routing policy thresholds
# Mirror the values in vol-idp-poc/config/routing-policy.json.
# Documents that do not satisfy ALL conditions are not forwarded
# to the Extraction SM (filtered by the EventBridge rule).
# ============================================================

variable "extraction_classifications" {
  type        = list(string)
  description = "Classification labels that are eligible for BDA extraction."
  default     = ["BANK_STATEMENT", "TRANSACTION_REPORT"]
}

variable "classification_confidence_threshold" {
  type        = number
  description = "Minimum classification confidence score (0–1) required to trigger extraction."
  default     = 0.75
}

variable "classification_max_pages" {
  type        = number
  description = "Maximum total page count for a document to be sent to BDA extraction."
  default     = 100
}

variable "classification_max_bytes" {
  type        = number
  description = "Maximum document size in bytes for BDA extraction (default 200 MB)."
  default     = 209715200
}

data "aws_region" "current" {}
data "aws_caller_identity" "current" {}

locals {
  name_prefix             = "vol-idp-${var.environment}"
  classification_model_id = "${var.bedrock_region_prefix}.${var.classification_model_id}"
}

# ============================================================
# S3 — Documents Bucket (upload target)
# Operators upload PDFs directly to this bucket. EventBridge
# integration fires an Object Created event for every upload,
# which the rule below routes straight to the Classification SM.
#
# Future improvement — GuardDuty seam:
#   When the MoveCleanDocument phase is migrated, the EventBridge
#   rule below should be removed and replaced with:
#     1. A GuardDuty rule that listens for NO_THREATS_FOUND events
#        on THIS bucket and triggers MoveCleanDocument.
#     2. A second bucket (clean-documents) that MoveCleanDocument
#        writes to, whose Object Created event triggers Classification.
#   The Classification SM and Lambda are unchanged by that addition.
# ============================================================
resource "aws_s3_bucket" "documents" {
  bucket = "${local.name_prefix}-documents"
}

resource "aws_s3_bucket_public_access_block" "documents" {
  bucket                  = aws_s3_bucket.documents.id
  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_server_side_encryption_configuration" "documents" {
  bucket = aws_s3_bucket.documents.id
  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_versioning" "documents" {
  bucket = aws_s3_bucket.documents.id
  versioning_configuration {
    status = "Enabled"
  }
}

# Forward all S3 object events to the default EventBridge bus.
resource "aws_s3_bucket_notification" "documents" {
  bucket      = aws_s3_bucket.documents.id
  eventbridge = true
}

# ============================================================
# CloudWatch — Lambda Log Group
# Created before the Lambda so Terraform controls retention
# rather than letting Lambda auto-create it with no expiry.
# ============================================================
resource "aws_cloudwatch_log_group" "classify_document" {
  name              = "/aws/lambda/${local.name_prefix}-classify-document"
  retention_in_days = 30
}

# ============================================================
# Lambda — classify-document
# GetObject → base64 → Bedrock InvokeModel (forced tool use)
# → small classification JSON. The round-trip lives in Lambda
# because Bedrock requires inline base64 PDFs, and a multi-page
# PDF exceeds Step Functions' 256 KB state limit.
# ============================================================
data "archive_file" "classify_document" {
  type        = "zip"
  source_dir  = "${path.module}/lambdas/classify-document"
  output_path = "${path.module}/lambdas/classify-document.zip"
}

resource "aws_lambda_function" "classify_document" {
  function_name = "${local.name_prefix}-classify-document"
  description   = "Classifies a PDF via Claude on Bedrock (InvokeModel + forced tool). GetObject → base64 → Bedrock → classification verdict."
  role          = aws_iam_role.classify_document_lambda.arn

  runtime          = "nodejs24.x"
  handler          = "index.handler"
  filename         = data.archive_file.classify_document.output_path
  source_code_hash = data.archive_file.classify_document.output_base64sha256
  timeout          = var.lambda_timeout
  memory_size      = var.lambda_memory_size

  environment {
    variables = {
      MODEL_ID       = local.classification_model_id
      BEDROCK_REGION = var.bedrock_region
    }
  }

  logging_config {
    log_format = "Text"
    log_group  = aws_cloudwatch_log_group.classify_document.name
  }

  depends_on = [aws_cloudwatch_log_group.classify_document]
}

# ============================================================
# CloudWatch — Step Functions Log Group
# Must be under /aws/vendedlogs/states/ so Step Functions has
# the resource-policy permissions it needs to write to it.
# ============================================================
resource "aws_cloudwatch_log_group" "classification_sm" {
  name              = "/aws/vendedlogs/states/${local.name_prefix}-classification"
  retention_in_days = 30
}

# ============================================================
# Step Functions — Classification State Machine
# The ASL template uses ${CLASSIFY_DOCUMENT_LAMBDA_ARN} as a
# definition of the ARN is found below.
# ============================================================
resource "aws_sfn_state_machine" "classification" {
  name     = "${local.name_prefix}-classification"
  role_arn = aws_iam_role.classification_sm.arn
  type     = "STANDARD"


  definition = templatefile("${path.module}/state-machines/classification.asl.json", {
    CLASSIFY_DOCUMENT_LAMBDA_ARN = aws_lambda_function.classify_document.arn
  })

  logging_configuration {
    log_destination        = "${aws_cloudwatch_log_group.classification_sm.arn}:*"
    include_execution_data = true
    level                  = "ALL"
  }

  tracing_configuration {
    enabled = true
  }

  depends_on = [aws_cloudwatch_log_group.classification_sm]
}

# ============================================================
# EventBridge — Trigger Classification SM on document upload
# S3 fires an "Object Created" event for every upload to the
# documents bucket. The input transformer reshapes the S3 event
# into the payload shape the Classification SM expects:
#   { bucket, key, object: { size, versionId }, config }
#
# Future improvement — GuardDuty seam:
#   Replace this rule with a GuardDuty NO_THREATS_FOUND rule so
#   only threat-scanned documents reach classification. See the
#   comment on aws_s3_bucket.documents for the full migration plan.
# ============================================================
resource "aws_cloudwatch_event_rule" "document_uploaded" {
  name        = "${local.name_prefix}-document-uploaded"
  description = "Trigger the Classification SM when a document is uploaded to the documents bucket"

  event_pattern = jsonencode({
    source      = ["aws.s3"]
    detail-type = ["Object Created"]
    detail = {
      bucket = {
        name = [aws_s3_bucket.documents.bucket]
      }
    }
  })
}

resource "aws_cloudwatch_event_target" "classification" {
  rule     = aws_cloudwatch_event_rule.document_uploaded.name
  arn      = aws_sfn_state_machine.classification.arn
  role_arn = aws_iam_role.eventbridge_invoke_classification.arn

  input_transformer {
    input_paths = {
      bucket     = "$.detail.bucket.name"
      key        = "$.detail.object.key"
      size       = "$.detail.object.size"
      version_id = "$.detail.object.version-id"
    }

    input_template = "{\"bucket\":\"<bucket>\",\"key\":\"<key>\",\"object\":{\"size\":<size>,\"versionId\":\"<version_id>\"},\"config\":{}}"
  }
}

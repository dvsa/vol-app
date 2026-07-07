data "aws_region" "current" {}
data "aws_caller_identity" "current" {}

locals {
  name_prefix             = "vol-idp-${var.environment}"
  classification_model_id = "${var.bedrock_region_prefix}.${var.classification_model_id}"
}

# ============================================================
# S3 — Reference documents bucket managed in vol-terraform (sabredav).
# ============================================================
data "aws_s3_bucket" "documents" {
  bucket = var.documents_bucket_name
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

data "archive_file" "classify_document" {
  type        = "zip"
  source_dir  = "${path.module}/lambdas/classify-document"
  output_path = "${path.module}/lambdas/classify-document/classify-document.zip"
}

resource "aws_lambda_function" "classify_document" {
  function_name = "${local.name_prefix}-classify-document"
  description   = "Classifies a PDF via Claude on Bedrock. GetObject → base64 → Bedrock → classification verdict."
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
# placeholder; Terraform's templatefile() injects the real ARN.
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
#
# S3 document bucket (sabredav) lives within vol-terraform
# S3 fires an "Object Created" event for every upload to the
# documents bucket. The input transformer reshapes the S3 event
# into the payload shape the Classification SM expects:
#   { bucket, key, object: { size, versionId }, config }
#
# Classification only runs when both conditions are met:
# 1. EventBridge rule: The object key matches the configured prefix (var.documents_key_prefix).
# 2. The Step Function (classification.asl.json): verifies the uploaded object is in the current
#    year/month using JSONata $now(). Objects outside the current year/month are skipped,
#    so no Terraform changes are needed when the date rolls over.
#
# Future migration step — GuardDuty seam:
#   When GuardDuty is added, replace this rule with a GuardDuty
#   NO_THREATS_FOUND rule and route through MoveCleanDocument first.
#
# ============================================================
resource "aws_cloudwatch_event_rule" "document_uploaded" {
  name        = "${local.name_prefix}-document-uploaded"
  description = "Trigger Classification SM for Financial Evidence Digital uploads (current year/month narrowed in the SM)"

  event_pattern = jsonencode({
    source      = ["aws.s3"]
    detail-type = ["Object Created"]
    detail = {
      bucket = {
        name = [var.documents_bucket_name]
      }
      object = {
        key = [{ prefix = var.documents_key_prefix }]
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

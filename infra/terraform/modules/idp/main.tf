data "aws_region" "current" {}
data "aws_caller_identity" "current" {}

locals {
  name_prefix             = "vol-idp-${var.environment}"
  classification_model_id = "${var.bedrock_region_prefix}.${var.classification_model_id}"

  # BDA cross-region inference profile ARN.
  # Format: arn:aws:bedrock:{region}:{account}:data-automation-profile/{prefix}.data-automation-v1
  bda_profile_arn = "arn:aws:bedrock:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:data-automation-profile/${var.bedrock_region_prefix}.data-automation-v1"
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

    input_template = "{\"bucket\":\"<bucket>\",\"key\":\"<key>\",\"object\":{\"size\":<size>,\"versionId\":\"<version_id>\"},\"config\":{\"outputBucket\":\"${aws_s3_bucket.idp_output.bucket}\",\"bedrockProjectArn\":\"${awscc_bedrock_data_automation_project.idp.project_arn}\",\"bedrockProfileArn\":\"${local.bda_profile_arn}\",\"bedrockProjectStage\":\"${var.bda_project_stage}\"}}"
  }
}

# ============================================================
# S3 — Output bucket for Bedrock Data Automation results
# BDA writes extraction results (result.json etc.) here.
# Objects are ephemeral pipeline artefacts; 30-day lifecycle
# cleans up automatically without manual intervention.
# ============================================================
resource "aws_s3_bucket" "idp_output" {
  bucket = "${local.name_prefix}-output"
}

resource "aws_s3_bucket_public_access_block" "idp_output" {
  bucket = aws_s3_bucket.idp_output.id

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true
}

resource "aws_s3_bucket_server_side_encryption_configuration" "idp_output" {
  bucket = aws_s3_bucket.idp_output.id

  rule {
    apply_server_side_encryption_by_default {
      sse_algorithm = "AES256"
    }
  }
}

resource "aws_s3_bucket_lifecycle_configuration" "idp_output" {
  bucket = aws_s3_bucket.idp_output.id

  rule {
    id     = "delete-old-outputs"
    status = "Enabled"

    expiration {
      days = 30
    }
  }
}

# ============================================================
# CloudWatch — Extraction SM Log Group
# ============================================================
resource "aws_cloudwatch_log_group" "extraction_sm" {
  name              = "/aws/vendedlogs/states/${local.name_prefix}-extraction"
  retention_in_days = 30
}

# ============================================================
# Step Functions — Extraction State Machine
# Triggered by DocumentProcessing-Classified events from the
# Classification SM. All runtime config (BDA project ARN,
# output bucket, profile ARN) flows in via $.config, populated
# by the Classification SM's EventBridge input transformer.
#
# The ASL has no template placeholders — all values are passed
# in at runtime through the event payload.
# ============================================================
resource "aws_sfn_state_machine" "extraction" {
  name     = "${local.name_prefix}-extraction"
  role_arn = aws_iam_role.extraction_sm.arn
  type     = "STANDARD"

  definition = file("${path.module}/state-machines/extraction.asl.json")

  logging_configuration {
    log_destination        = "${aws_cloudwatch_log_group.extraction_sm.arn}:*"
    include_execution_data = true
    level                  = "ALL"
  }

  tracing_configuration {
    enabled = true
  }

  depends_on = [aws_cloudwatch_log_group.extraction_sm]
}

# ============================================================
# EventBridge — Trigger Extraction SM on classified documents
#
# Listens for DocumentProcessing-Classified events emitted by
# the Classification SM on the default event bus. The rule
# applies the routing policy thresholds from routing-policy.json
# so only eligible documents (correct classification, sufficient
# confidence, within page/size limits) reach the Extraction SM.
#
# The input_path "$.detail" passes the full classification event
# detail directly as the SM input — config (outputBucket,
# bedrockProjectArn, bedrockProfileArn, bedrockProjectStage)
# is already present in $.detail.config from the earlier
# Classification SM trigger.
# ============================================================
resource "aws_cloudwatch_event_rule" "classified" {
  name        = "${local.name_prefix}-classified"
  description = "Trigger Extraction SM for classified bank statements / transaction reports meeting routing thresholds"

  event_pattern = jsonencode({
    source      = ["custom.documentProcessing"]
    detail-type = ["DocumentProcessing-Classified"]
    detail = {
      classification           = var.extraction_classifications
      classificationConfidence = [{ numeric = [">=", var.classification_confidence_threshold] }]
      totalPages               = [{ numeric = ["<=", var.classification_max_pages] }]
      documentSizeBytes        = [{ numeric = ["<=", var.classification_max_bytes] }]
    }
  })
}

resource "aws_cloudwatch_event_target" "extraction" {
  rule     = aws_cloudwatch_event_rule.classified.name
  arn      = aws_sfn_state_machine.extraction.arn
  role_arn = aws_iam_role.eventbridge_invoke_extraction.arn

  # Pass the full event detail directly as the SM input.
  # $.detail already contains bucket, key, object, config,
  # classification, confidence, page/size metadata.
  input_path = "$.detail"
}

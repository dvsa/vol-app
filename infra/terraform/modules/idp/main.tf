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
    include_execution_data = false
    level                  = "ERROR"
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
# Called by the AnalyseFinancialDocument orchestrator SM via
# startExecution.sync:2. All runtime config (BDA project ARN,
# output bucket, profile ARN) flows in via $.config from the
# orchestrator's BuildExtractionInput state.
#
# The ASL has no template placeholders — all values are passed
# in at runtime through the execution input.
# ============================================================
resource "aws_sfn_state_machine" "extraction" {
  name     = "${local.name_prefix}-extraction"
  role_arn = aws_iam_role.extraction_sm.arn
  type     = "STANDARD"

  definition = file("${path.module}/state-machines/extraction.asl.json")

  logging_configuration {
    log_destination        = "${aws_cloudwatch_log_group.extraction_sm.arn}:*"
    include_execution_data = false
    level                  = "ERROR"
  }

  tracing_configuration {
    enabled = true
  }

  depends_on = [aws_cloudwatch_log_group.extraction_sm]
}

# ============================================================
# Lambda — extract-s3-json-field
# Plucks individual fields from BDA output JSON files in S3,
# keeping Step Functions state well within the 256 KB limit.
# Used by the AI Analysis SM to fetch inference_result and the
# document markdown without loading the full result.json.
# ============================================================
resource "aws_cloudwatch_log_group" "extract_s3_json_field" {
  name              = "/aws/lambda/${local.name_prefix}-extract-s3-json-field"
  retention_in_days = 30
}

data "archive_file" "extract_s3_json_field" {
  type        = "zip"
  source_dir  = "${path.module}/lambdas/extract-s3-json-field"
  output_path = "${path.module}/lambdas/extract-s3-json-field/extract-s3-json-field.zip"
}

resource "aws_lambda_function" "extract_s3_json_field" {
  function_name = "${local.name_prefix}-extract-s3-json-field"
  description   = "Reads a JSON object from S3 and returns either the whole parsed object or a sub-field at a dot-separated path. Used to keep Step Functions state within the 256 KB limit when reading BDA output files."
  role          = aws_iam_role.extract_s3_json_field_lambda.arn

  # tflint-ignore: aws_lambda_function_invalid_runtime
  runtime          = "nodejs24.x"
  handler          = "index.handler"
  filename         = data.archive_file.extract_s3_json_field.output_path
  source_code_hash = data.archive_file.extract_s3_json_field.output_base64sha256
  timeout          = var.lambda_timeout
  memory_size      = var.lambda_memory_size

  logging_config {
    log_format = "Text"
    log_group  = aws_cloudwatch_log_group.extract_s3_json_field.name
  }

  depends_on = [aws_cloudwatch_log_group.extract_s3_json_field]
}

# ============================================================
# CloudWatch — AI Analysis SM Log Group
# ============================================================
resource "aws_cloudwatch_log_group" "ai_analysis_sm" {
  name              = "/aws/vendedlogs/states/${local.name_prefix}-ai-analysis"
  retention_in_days = 30
}

# ============================================================
# Step Functions — AI Analysis State Machine
# Called by the AnalyseFinancialDocument orchestrator SM via
# startExecution.sync:2. Fetches BDA output from S3, sends it
# to a Bedrock managed prompt, then emits
# DocumentProcessing-AnalysisCompleted or AnalysisFailed on
# the default event bus and returns an AnalysisSucceeded/
# AnalysisFailed output for the orchestrator.
# ============================================================
resource "aws_sfn_state_machine" "ai_analysis" {
  name     = "${local.name_prefix}-ai-analysis"
  role_arn = aws_iam_role.ai_analysis_sm.arn
  type     = "STANDARD"

  definition = templatefile("${path.module}/state-machines/ai-analysis.asl.json", {
    EXTRACT_S3_JSON_FIELD_LAMBDA_ARN = aws_lambda_function.extract_s3_json_field.arn
    BEDROCK_PROMPT_VERSION_ARN       = awscc_bedrock_prompt_version.bank_statement_check.arn
  })

  logging_configuration {
    log_destination        = "${aws_cloudwatch_log_group.ai_analysis_sm.arn}:*"
    include_execution_data = false
    level                  = "ERROR"
  }

  tracing_configuration {
    enabled = true
  }

  depends_on = [aws_cloudwatch_log_group.ai_analysis_sm]
}

# ============================================================
# Step Functions — AnalyseFinancialDocument Orchestrator SM
# Triggered by FinancialEvidenceDocumentSubmitted from vol.api.
# Orchestrates the full IDP pipeline: check/run classification,
# run extraction, run AI analysis, emit FinancialDocumentAnalysed.
# ============================================================
resource "aws_cloudwatch_log_group" "analyse_financial_document_sm" {
  name              = "/aws/vendedlogs/states/${local.name_prefix}-analyse-financial-document"
  retention_in_days = 30
}

resource "aws_sfn_state_machine" "analyse_financial_document" {
  name     = "${local.name_prefix}-analyse-financial-document"
  role_arn = aws_iam_role.analyse_financial_document_sm.arn
  type     = "STANDARD"

  definition = templatefile("${path.module}/state-machines/analyse-financial-document.asl.json", {
    CLASSIFICATION_SM_ARN = aws_sfn_state_machine.classification.arn
    EXTRACTION_SM_ARN     = aws_sfn_state_machine.extraction.arn
    AI_ANALYSIS_SM_ARN    = aws_sfn_state_machine.ai_analysis.arn
    OUTPUT_BUCKET         = aws_s3_bucket.idp_output.bucket
    BDA_PROJECT_ARN       = awscc_bedrock_data_automation_project.idp.project_arn
    BDA_PROFILE_ARN       = local.bda_profile_arn
    BDA_PROJECT_STAGE     = var.bda_project_stage
    # Render as a JSONata array using single-quoted strings so it can be safely embedded in the ASL condition.
    EXTRACTION_CLASSIFICATIONS          = "[${join(", ", formatlist("'%s'", var.extraction_classifications))}]"
    CLASSIFICATION_CONFIDENCE_THRESHOLD = tostring(var.classification_confidence_threshold)
    CLASSIFICATION_MAX_PAGES            = tostring(var.classification_max_pages)
    CLASSIFICATION_MAX_BYTES            = tostring(var.classification_max_bytes)
  })

  logging_configuration {
    log_destination        = "${aws_cloudwatch_log_group.analyse_financial_document_sm.arn}:*"
    include_execution_data = false
    level                  = "ERROR"
  }

  tracing_configuration {
    enabled = true
  }

  depends_on = [aws_cloudwatch_log_group.analyse_financial_document_sm]
}

# ============================================================
# EventBridge — Trigger orchestrator on FinancialEvidenceDocumentSubmitted
#
# vol.api emits this event when a financial evidence document
# is submitted for IDP analysis. The event detail contains
# the document location (bucket/key), analysis_token, and
# applicant_profile. The orchestrator injects BDA config
# (output bucket, project ARN etc.) from Terraform template vars
# in its first state, so the EventBridge rule passes the full
# event detail as-is using input_path "$.detail".
# ============================================================
resource "aws_cloudwatch_event_rule" "financial_evidence_submitted" {
  name        = "${local.name_prefix}-financial-evidence-submitted"
  description = "Trigger AnalyseFinancialDocument orchestrator SM when vol.api submits a financial evidence document for IDP analysis"

  event_pattern = jsonencode({
    source      = ["vol.api"]
    detail-type = ["FinancialEvidenceDocumentSubmitted"]
  })
}

resource "aws_cloudwatch_event_target" "analyse_financial_document" {
  rule     = aws_cloudwatch_event_rule.financial_evidence_submitted.name
  arn      = aws_sfn_state_machine.analyse_financial_document.arn
  role_arn = aws_iam_role.eventbridge_invoke_analyse_financial_document.arn

  input_path = "$.detail"
}

# The AnalyseFinancialDocument SM emits DocumentProcessing-FinancialDocumentAnalysed when
# the pipeline finishes. The detail contains analysis_token and execution_arn.
# EventBridge submits an AWS Batch job to store the result using those values
# as command arguments, overriding the default container command.
locals {
  # Batch ARNs follow the naming conventions established by the service module.
  store_result_batch_queue_arn   = "arn:aws:batch:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:job-queue/vol-app-${var.environment}-default"
  store_result_batch_job_def_arn = "arn:aws:batch:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:job-definition/vol-app-${var.environment}-idp-store-document-analysis-result"
}

resource "aws_cloudwatch_event_rule" "financial_document_analysed" {
  name        = "${local.name_prefix}-financial-document-analysed"
  description = "Trigger idp-store-document-analysis-result Batch job when the orchestrator SM completes financial document analysis"

  event_pattern = jsonencode({
    source      = ["custom.documentProcessing"]
    detail-type = ["DocumentProcessing-FinancialDocumentAnalysed"]
  })
}

resource "aws_cloudwatch_event_target" "store_document_analysis_result" {
  rule     = aws_cloudwatch_event_rule.financial_document_analysed.name
  arn      = local.store_result_batch_queue_arn
  role_arn = aws_iam_role.eventbridge_invoke_store_result.arn

  batch_target {
    job_definition = local.store_result_batch_job_def_arn
    job_name       = "${local.name_prefix}-store-document-analysis-result"
  }

  # Pass analysis_token and execution_arn as container command overrides so the
  # Batch job can find the correct item in the table from the analysis_token and
  # retrieve the S3 bucket and S3 key from the execution_arn from AnalyseFinancialDocument SM
  input_transformer {
    input_paths = {
      analysis_token = "$.detail.analysis_token"
      execution_arn  = "$.detail.execution_arn"
    }
    input_template = "{\"containerOverrides\":{\"command\":[\"/var/www/html/vendor/bin/laminas\",\"--container=/var/www/html/config/container-cli.php\",\"-v\",\"idp:store-document-analysis-result\",\"--analysis-token=<analysis_token>\",\"--execution-arn=<execution_arn>\"]}}"
  }
}

# ============================================================
# Bedrock Managed Prompt — Bank Statement Quality Check
#
# The system prompt embeds the DVSA validation rules JSON and
# the tool schema so they are cached server-side via the
# cachePoint on the system block (reduces cost and latency on
# repeated invocations). Tool use is forced to submit_quality_check
# so the model output is validated against the JSON Schema.
#
# Versioning is automatic — awscc_bedrock_prompt_version is replaced whenever
# the prompt content hash changes (see terraform_data.prompt_content_hash).
# ============================================================
locals {
  analysis_inference_profile_arn = "arn:aws:bedrock:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:inference-profile/${var.bedrock_region_prefix}.${var.analysis_model_id}"

  bank_statement_checks = file("${path.module}/config/bank-statement-checks.json")
  tool_input_schema     = jsondecode(file("${path.module}/config/bank-statement-check-tool-schema.json"))

  analysis_system_text = join("\n", [
    "You are a financial document validator for the UK DVSA (Driver and Vehicle Standards Agency) Vehicle Operator Licensing system. This is a DVSA digital service provided for the Office of the Traffic Commissioner (OTC). Your role is to perform official Bank Statement Quality Checks using VOL business rules and provide structured analysis for OTC caseworkers.",
    "",
    "The validation rules below are OFFICIAL DVSA REGULATIONS. Follow them exactly as written. Do not apply your own logic, common sense, or make assumptions. If a rule specifies how to evaluate something, use that method precisely. Do not invent additional requirements or constraints.",
    "",
    "<dvsa_validation_rules>",
    local.bank_statement_checks,
    "</dvsa_validation_rules>",
  ])

  analysis_user_template = join("\n", [
    "<applicant_profile>",
    "{{applicant_profile}}",
    "</applicant_profile>",
    "",
    "<bank_statement_structured_data>",
    "Extracted fields from the bank statement: opening/closing balances, transaction details, account summary.",
    "{{bank_statement_data}}",
    "</bank_statement_structured_data>",
    "",
    "<bank_statement_document>",
    "Full document content in markdown for visual/contextual checks (letterhead, authenticity, completeness).",
    "{{bank_statement_markdown}}",
    "</bank_statement_document>",
    "",
    "<extraction_context>",
    "Metadata about how this document was classified and extracted. UNCLASSIFIED pages are noise (Textract could not classify them) - they are excluded from confidence but kept in pageBreakdown for visibility. If totalPages is much larger than dominantTypePages, the upload contained other content alongside the bank statement (UNCLASSIFIED noise pages, an overdraft letter, an unrelated document, another bank statement). Surface this in remark for caseworker visibility.",
    "{{extraction_context}}",
    "</extraction_context>",
    "",
    "You are reviewing this bank statement for an OTC caseworker. Evaluate it against the rules and call submit_quality_check exactly once with results for all 11 checks (FI01-FI16). Follow the executionFlowOrder for sequencing and skip conditions. For skipped checks include the skip reason in remark. Show step-by-step reasoning for every check in workingOut. If extraction_context indicates the upload was a mixed/bundled document, add a remark noting that other segments were ignored and recommend the caseworker verify whether they need separate review.",
  ])

  max_token_usage = 16000

  # Hash of all prompt-defining inputs. When this changes, terraform_data.prompt_content_hash
  # is replaced, which triggers replacement of awscc_bedrock_prompt_version (a new immutable
  # version is published). Covers: system text, user template, tool schema, model, max_tokens.
  prompt_content_hash = sha256(join("||", [
    local.analysis_system_text,
    local.analysis_user_template,
    jsonencode(local.tool_input_schema),
    local.analysis_inference_profile_arn,
    tostring(local.max_token_usage),
  ]))
}

resource "awscc_bedrock_prompt" "bank_statement_check" {
  name            = "${local.name_prefix}-bank-statement-check"
  description     = "DVSA Bank Statement Quality Checks (rules + tool schema baked in, prompt caching on system block)"
  default_variant = "default"

  variants = [
    {
      name          = "default"
      model_id      = local.analysis_inference_profile_arn
      template_type = "CHAT"

      template_configuration = {
        chat = {
          system = [
            { text = local.analysis_system_text },
            { cache_point = { type = "default" } },
          ]

          messages = [
            {
              role    = "user"
              content = [{ text = local.analysis_user_template }]
            },
          ]

          tool_configuration = {
            tools = [
              {
                tool_spec = {
                  name        = "submit_quality_check"
                  description = "Submit the completed bank statement quality check results. Call exactly once with result, workingOut, and remark for every one of the 11 checks (FI01-FI16). Skipped checks must include the skip reason in remark."
                  input_schema = {
                    json = jsonencode(local.tool_input_schema)
                  }
                }
              }
            ]
            tool_choice = {
              tool = { name = "submit_quality_check" }
            }
          }

          input_variables = [
            { name = "applicant_profile" },
            { name = "bank_statement_data" },
            { name = "bank_statement_markdown" },
            { name = "extraction_context" },
          ]
        }
      }

      inference_configuration = {
        text = {
          max_tokens = local.max_token_usage
        }
      }
    }
  ]
}

# Tracks the prompt content hash. Replaced whenever prompt_content_hash changes,
# which in turn triggers replacement of awscc_bedrock_prompt_version below.
resource "terraform_data" "prompt_content_hash" {
  input = local.prompt_content_hash
}

# Publishes an immutable Bedrock prompt version. Replaced (new version published)
# only when terraform_data.prompt_content_hash is replaced, i.e. the prompt
# definition has changed. create_before_destroy ensures the new version exists
# before the SM is updated and the old version is cleaned up.
resource "awscc_bedrock_prompt_version" "bank_statement_check" {
  prompt_arn  = awscc_bedrock_prompt.bank_statement_check.arn
  description = "Published from content hash ${local.prompt_content_hash}"

  lifecycle {
    replace_triggered_by  = [terraform_data.prompt_content_hash]
    create_before_destroy = true
  }
}

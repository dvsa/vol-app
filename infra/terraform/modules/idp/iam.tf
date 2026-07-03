# ============================================================
# IAM — Lambda execution role (classify-document)
# ============================================================
data "aws_iam_policy_document" "lambda_assume_role" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["lambda.amazonaws.com"]
    }
  }
}

resource "aws_iam_role" "classify_document_lambda" {
  name               = "${local.name_prefix}-classify-document-lambda"
  assume_role_policy = data.aws_iam_policy_document.lambda_assume_role.json
}

data "aws_iam_policy_document" "classify_document_lambda" {
  statement {
    sid = "CloudWatchLogs"
    actions = [
      "logs:CreateLogStream",
      "logs:PutLogEvents",
    ]
    resources = ["${aws_cloudwatch_log_group.classify_document.arn}:*"]
  }

  # Read access on the documents bucket — HeadObject for the size pre-flight,
  # GetObject to stream the PDF bytes for inline base64 encoding.
  statement {
    sid = "S3ReadDocuments"
    actions = [
      "s3:GetObject",
      "s3:HeadObject",
    ]
    resources = [
      aws_s3_bucket.documents.arn,
      "${aws_s3_bucket.documents.arn}/*",
    ]
  }

  # Bedrock InvokeModel scoped to '*' to cover the foundation models
  # a cross-region inference profile (eu.*) fans out to.
  statement {
    sid       = "BedrockInvokeClassificationModel"
    actions   = ["bedrock:InvokeModel"]
    resources = ["*"]
  }
}

resource "aws_iam_role_policy" "classify_document_lambda" {
  name   = "${local.name_prefix}-classify-document-lambda"
  role   = aws_iam_role.classify_document_lambda.id
  policy = data.aws_iam_policy_document.classify_document_lambda.json
}

# ============================================================
# IAM — Step Functions execution role (Classification SM)
# ============================================================
data "aws_iam_policy_document" "sfn_assume_role" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["states.amazonaws.com"]
    }
  }
}

resource "aws_iam_role" "classification_sm" {
  name               = "${local.name_prefix}-classification-sm"
  assume_role_policy = data.aws_iam_policy_document.sfn_assume_role.json
}

data "aws_iam_policy_document" "classification_sm" {
  statement {
    sid     = "InvokeClassifyDocumentLambda"
    actions = ["lambda:InvokeFunction"]
    resources = [
      aws_lambda_function.classify_document.arn,
      "${aws_lambda_function.classify_document.arn}:*",
    ]
  }

  # HeadObject (IsSupportedContentType), GetObjectTagging + PutObjectTagging
  # (BuildUpdatedTagSet / TagWithClassification).
  statement {
    sid = "S3DocumentOperations"
    actions = [
      "s3:HeadObject",
      "s3:GetObjectTagging",
      "s3:PutObjectTagging",
    ]
    resources = [
      aws_s3_bucket.documents.arn,
      "${aws_s3_bucket.documents.arn}/*",
    ]
  }

  # Emit DocumentProcessing-Classified / DocumentProcessing-ClassificationFailed
  # events to the default EventBridge bus.
  statement {
    sid     = "EventBridgePutEvents"
    actions = ["events:PutEvents"]
    resources = [
      "arn:aws:events:${data.aws_region.current.region}:${data.aws_caller_identity.current.account_id}:event-bus/default",
    ]
  }

  # Step Functions requires these to create and manage log deliveries.
  statement {
    sid = "CloudWatchLogs"
    actions = [
      "logs:CreateLogDelivery",
      "logs:CreateLogStream",
      "logs:GetLogDelivery",
      "logs:UpdateLogDelivery",
      "logs:DeleteLogDelivery",
      "logs:ListLogDeliveries",
      "logs:PutLogEvents",
      "logs:PutResourcePolicy",
      "logs:DescribeResourcePolicies",
      "logs:DescribeLogGroups",
    ]
    resources = ["*"]
  }

  statement {
    sid = "XRayTracing"
    actions = [
      "xray:PutTraceSegments",
      "xray:PutTelemetryRecords",
      "xray:GetSamplingRules",
      "xray:GetSamplingTargets",
    ]
    resources = ["*"]
  }
}

resource "aws_iam_role_policy" "classification_sm" {
  name   = "${local.name_prefix}-classification-sm"
  role   = aws_iam_role.classification_sm.id
  policy = data.aws_iam_policy_document.classification_sm.json
}

# ============================================================
# IAM — EventBridge role (start Classification SM execution)
# ============================================================
data "aws_iam_policy_document" "eventbridge_assume_role" {
  statement {
    actions = ["sts:AssumeRole"]
    principals {
      type        = "Service"
      identifiers = ["events.amazonaws.com"]
    }
  }
}

resource "aws_iam_role" "eventbridge_invoke_classification" {
  name               = "${local.name_prefix}-eventbridge-invoke-classification"
  assume_role_policy = data.aws_iam_policy_document.eventbridge_assume_role.json
}

data "aws_iam_policy_document" "eventbridge_invoke_classification" {
  statement {
    sid       = "StartClassificationExecution"
    actions   = ["states:StartExecution"]
    resources = [aws_sfn_state_machine.classification.arn]
  }
}

resource "aws_iam_role_policy" "eventbridge_invoke_classification" {
  name   = "${local.name_prefix}-eventbridge-invoke-classification"
  role   = aws_iam_role.eventbridge_invoke_classification.id
  policy = data.aws_iam_policy_document.eventbridge_invoke_classification.json
}

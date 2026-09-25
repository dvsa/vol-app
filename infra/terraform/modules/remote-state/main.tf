data "aws_region" "current" {}

data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
  identifier = var.environment != null ? "${var.identifier}-${local.account_id}-${var.environment}-terraform-state" : "${var.identifier}-${local.account_id}-terraform-state"
}

resource "aws_kms_key" "dynamodb_table" {
  description         = "KMS key for the ${local.identifier}-lock DynamoDB table"
  enable_key_rotation = true

  policy = jsonencode({
    Version = "2012-10-17"
    Statement = [
      {
        Sid    = "EnableRootPermissions"
        Effect = "Allow"
        Principal = {
          AWS = "arn:aws:iam::${local.account_id}:root"
        }
        Action   = "kms:*"
        Resource = "*"
      },
      {
        Sid    = "AllowDynamoDBUseOfTheKey"
        Effect = "Allow"
        Principal = {
          Service = "dynamodb.amazonaws.com"
        }
        Action = [
          "kms:CreateGrant",
          "kms:Decrypt",
          "kms:DescribeKey",
          "kms:Encrypt",
          "kms:GenerateDataKey*",
          "kms:ReEncrypt*"
        ]
        Resource = "*"
        Condition = {
          StringEquals = {
            "kms:CallerAccount" = local.account_id
            "kms:ViaService"    = "dynamodb.${data.aws_region.current.name}.amazonaws.com"
          }
          Bool = {
            "kms:GrantIsForAWSResource" = "true"
          }
        }
      }
    ]
  })
}

resource "aws_kms_alias" "dynamodb_table" {
  name          = "alias/${local.identifier}-lock"
  target_key_id = aws_kms_key.dynamodb_table.key_id
}

module "s3" {
  count = var.create_bucket ? 1 : 0

  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

  bucket = local.identifier

  attach_deny_insecure_transport_policy = true

  lifecycle_rule = [{
    id = "lifecycle"

    abort_incomplete_multipart_upload_days = 7

    noncurrent_version_expiration = {
      noncurrent_days = 90
    }

    status = "Enabled"
  }]

  server_side_encryption_configuration = {
    rule = {
      apply_server_side_encryption_by_default = {
        sse_algorithm = "aws:kms"
      }
    }
  }

  # S3 Bucket Ownership Controls
  control_object_ownership = true
  object_ownership         = "BucketOwnerEnforced"

  versioning = {
    enabled = true
  }
}

module "dynamodb_table" {
  source  = "terraform-aws-modules/dynamodb-table/aws"
  version = "~> 4.0"

  name                               = "${local.identifier}-lock"
  hash_key                           = "LockID"
  point_in_time_recovery_enabled     = true
  server_side_encryption_enabled     = true
  server_side_encryption_kms_key_arn = aws_kms_key.dynamodb_table.arn

  attributes = [
    {
      name = "LockID"
      type = "S"
    }
  ]
}

module "dynamodb_state_lock_policy" {
  count = var.create_dynamodb_policy ? 1 : 0

  source  = "terraform-aws-modules/iam/aws//modules/iam-policy"
  version = "~> 5.28"

  name        = "${local.identifier}-lock-policy"
  description = "Policy to allow access to the Terraform state lock"

  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Effect = "Allow",
        Action = [
          "dynamodb:DescribeTable",
          "dynamodb:GetItem",
          "dynamodb:PutItem",
          "dynamodb:DeleteItem"
        ]
        Resource = module.dynamodb_table.dynamodb_table_arn
      }
    ]
  })
}

module "s3_state_policy" {
  count = var.create_bucket && var.create_bucket_policy ? 1 : 0

  source  = "terraform-aws-modules/iam/aws//modules/iam-policy"
  version = "~> 5.28"

  name        = "${local.identifier}-policy"
  description = "Policy to allow access to the Terraform state in S3"

  policy = jsonencode({
    Version = "2012-10-17",
    Statement = [
      {
        Effect = "Allow",
        Action = [
          "s3:GetObject",
          "s3:PutObject",
          "s3:ListBucket"
        ]
        Resource = module.s3[0].s3_bucket_arn
      }
    ]
  })
}

data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
  identifier = var.environment != null ? "${var.identifier}-${local.account_id}-${var.environment}-terraform-state" : "${var.identifier}-${local.account_id}-terraform-state"
}

module "s3" {
  count = var.create_bucket ? 1 : 0

  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 5.10"

  bucket = local.identifier

  attach_deny_insecure_transport_policy = true

  lifecycle_rule = [{
    id = "lifecycle"

    noncurrent_version_expiration = {
      noncurrent_days = 90
    }

    status = "Enabled"
  }]

  server_side_encryption_configuration = {
    rule = {
      apply_server_side_encryption_by_default = {
        sse_algorithm = "AES256"
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
  version = "~> 5.5"

  name     = "${local.identifier}-lock"
  hash_key = "LockID"

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
  version = "~> 6.4"

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
  version = "~> 6.4"

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

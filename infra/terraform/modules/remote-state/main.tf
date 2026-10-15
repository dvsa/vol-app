data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
  identifier = var.environment != null ? "${var.identifier}-${local.account_id}-${var.environment}-terraform-state" : "${var.identifier}-${local.account_id}-terraform-state"
}

#checkov:skip=CKV_AWS_19: Encryption is configured via the module input, but Checkov does not resolve it through this external module.
#checkov:skip=CKV_AWS_21: Versioning is configured via the module input, but Checkov does not resolve it through this external module.
module "s3" {
  count = var.create_bucket ? 1 : 0

  source = "git::https://github.com/terraform-aws-modules/terraform-aws-s3-bucket.git?ref=fccafe509c9c9af4646a4bc45387f63d83c8a006"
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

#checkov:skip=CKV_AWS_119: Terraform state lock tables intentionally use AWS-managed encryption and do not require a customer-managed KMS key.
module "dynamodb_table" {
  source   = "git::https://github.com/terraform-aws-modules/terraform-aws-dynamodb-table.git?ref=b6cc515760466a455ff0acb97b16151fdca4511e"
  name     = "${local.identifier}-lock"
  hash_key = "LockID"

  point_in_time_recovery_enabled        = true
  point_in_time_recovery_period_in_days = 35

  attributes = [
    {
      name = "LockID"
      type = "S"
    }
  ]
}

module "dynamodb_state_lock_policy" {
  count = var.create_dynamodb_policy ? 1 : 0

  source      = "git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-policy?ref=2eb955d1e9dcbb471ee1635e25e9c0db55df105a"
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

  source      = "git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-policy?ref=2eb955d1e9dcbb471ee1635e25e9c0db55df105a"
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

data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
  identifier = var.environment != null ? "${var.identifier}-${local.account_id}-${var.environment}-terraform-state" : "${var.identifier}-${local.account_id}-terraform-state"
}

module "s3_input" {
  count = var.create_bucket ? 1 : 0

  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

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
  version = "~> 4.0"

  name     = "${local.identifier}-lock"
  hash_key = "LockID"

  attributes = [
    {
      name = "LockID"
      type = "S"
    }
  ]

  point_in_time_recovery_enabled = true
}

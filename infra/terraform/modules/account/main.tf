data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
}

module "assets" {
  count = var.create_assets_bucket ? 1 : 0

  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

  bucket = "${local.account_id}-vol-app-assets"
}

data "aws_iam_policy_document" "s3_policy" {
  statement {
    actions   = ["s3:GetObject", "s3:ListBucket"]
    resources = ["${module.assets[0].s3_bucket_arn}/*"]

    principals {
      type        = "Service"
      identifiers = ["cloudfront.amazonaws.com"]
    }
    principals {
      type        = "AWS"
      identifiers = ["arn:aws:sts::054614622558:assumed-role/OLCS-DEVAPPCI-DEVCI-OLCSCISLAVE"]
    }
  }
}

resource "aws_s3_bucket_policy" "bucket_policy" {
  bucket = module.assets[0].s3_bucket_id
  policy = data.aws_iam_policy_document.s3_policy.json
}

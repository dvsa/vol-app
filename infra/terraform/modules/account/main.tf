data "aws_caller_identity" "current" {}

locals {
  account_id = data.aws_caller_identity.current.account_id
}

module "assets" {
  count = var.create_assets_bucket ? 1 : 0

  source = "git::https://github.com/terraform-aws-modules/terraform-aws-s3-bucket.git?ref=fccafe509c9c9af4646a4bc45387f63d83c8a006"
  bucket = "${local.account_id}-vol-app-assets"
}

data "aws_iam_policy_document" "s3_policy" {
  statement {
    actions   = ["s3:GetObject"]
    resources = ["${module.assets[0].s3_bucket_arn}/*"]

    principals {
      type        = "Service"
      identifiers = ["cloudfront.amazonaws.com"]
    }
  }
}

resource "aws_s3_bucket_policy" "bucket_policy" {
  bucket = module.assets[0].s3_bucket_id
  policy = data.aws_iam_policy_document.s3_policy.json
}

locals {
  maintenance_subdomain = "maintenance-${var.environment}"
  maintenance_domain    = "${local.maintenance_subdomain}.${var.domain_name}"
  bucket_name           = "vol-app-${var.environment}-maintenance"
  log_bucket_name       = "vol-app-${var.environment}-maintenance-logs"
  oac_id                = "maintenance_oac_${var.environment}"
}

# S3 bucket policy - only CloudFront access needed
data "aws_iam_policy_document" "maintenance_bucket_policy" {
  statement {
    sid    = "AllowCloudFrontOACAccess"
    effect = "Allow"

    principals {
      type        = "Service"
      identifiers = ["cloudfront.amazonaws.com"]
    }

    actions = ["s3:GetObject"]

    resources = ["${module.maintenance_bucket.s3_bucket_arn}/*"]

    condition {
      test     = "StringEquals"
      variable = "AWS:SourceArn"
      values   = ["arn:aws:cloudfront::${data.aws_caller_identity.current_account_id.account_id}:origin-access-control/${local.oac_id}"]
    }
  }
}

module "maintenance_bucket" {
  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

  bucket = local.bucket_name

  # No website configuration needed - CloudFront handles web serving
  # CORS not needed since CloudFront handles all requests

  block_public_acls       = true
  block_public_policy     = true
  ignore_public_acls      = true
  restrict_public_buckets = true

  control_object_ownership = true
  object_ownership         = "BucketOwnerPreferred"

  attach_policy = true
  policy        = data.aws_iam_policy_document.maintenance_bucket_policy.json

  force_destroy = true
}

# ACM Certificate for maintenance domain
module "maintenance_acm" {
  source  = "terraform-aws-modules/acm/aws"
  version = "~> 5.0"

  domain_name = local.maintenance_domain
  zone_id     = data.aws_route53_zone.public.id

  validation_method = "DNS"

  create_route53_records  = false
  validation_record_fqdns = module.maintenance_route53_records.validation_route53_record_fqdns

  providers = {
    aws = aws.acm
  }
}

module "maintenance_route53_records" {
  source  = "terraform-aws-modules/acm/aws"
  version = "~> 5.0"

  create_certificate          = false
  create_route53_records_only = true

  validation_method = "DNS"

  distinct_domain_names = module.maintenance_acm.distinct_domain_names
  zone_id               = data.aws_route53_zone.public.id

  acm_certificate_domain_validation_options = module.maintenance_acm.acm_certificate_domain_validation_options
}

# CloudFront distribution for maintenance page
module "maintenance_cloudfront" {
  source  = "terraform-aws-modules/cloudfront/aws"
  version = "~> 3.0"

  aliases = [local.maintenance_domain]

  http_version    = "http2and3"
  price_class     = "PriceClass_100"
  is_ipv6_enabled = true

  create_origin_access_control = true
  origin_access_control = {
    (local.oac_id) = {
      description      = "CloudFront access to maintenance S3 bucket"
      origin_type      = "s3"
      signing_behavior = "always"
      signing_protocol = "sigv4"
    }
  }

  origin = {
    (local.oac_id) = {
      domain_name           = module.maintenance_bucket.s3_bucket_bucket_regional_domain_name
      origin_access_control = local.oac_id
      origin_path           = ""
    }
  }

  default_cache_behavior = {
    target_origin_id       = local.oac_id
    viewer_protocol_policy = "redirect-to-https"
    allowed_methods        = ["GET", "HEAD", "OPTIONS"]
    cached_methods         = ["GET", "HEAD"]

    use_forwarded_values = false

    cache_policy_name            = "Managed-CachingDisabled"
    response_headers_policy_name = "Managed-CORS-with-preflight-and-SecurityHeadersPolicy"
  }

  logging_config = {
    bucket = module.maintenance_log_bucket.s3_bucket_bucket_domain_name
    prefix = "cloudfront"
  }

  viewer_certificate = {
    acm_certificate_arn = module.maintenance_acm.acm_certificate_arn
    ssl_support_method  = "sni-only"
  }
}

# S3 bucket for CloudFront logs
module "maintenance_log_bucket" {
  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 4.0"

  bucket = local.log_bucket_name

  control_object_ownership = true
  object_ownership         = "ObjectWriter"

  grant = [{
    type       = "CanonicalUser"
    permission = "FULL_CONTROL"
    id         = data.aws_canonical_user_id.current.id
    }, {
    # https://github.com/terraform-providers/terraform-provider-aws/issues/12512
    # https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/AccessLogs.html
    type       = "CanonicalUser"
    permission = "FULL_CONTROL"
    id         = data.aws_cloudfront_log_delivery_canonical_user_id.cloudfront.id
  }]

  force_destroy = true
}

# CloudFront invalidation policy for GitHub Actions
data "aws_iam_policy_document" "maintenance_cloudfront_invalidation" {
  statement {
    sid    = "AllowCloudFrontInvalidation"
    effect = "Allow"

    actions = [
      "cloudfront:CreateInvalidation",
      "cloudfront:GetInvalidation",
      "cloudfront:ListInvalidations"
    ]

    resources = [module.maintenance_cloudfront.cloudfront_distribution_arn]
  }
}

resource "aws_iam_policy" "maintenance_cloudfront_invalidation" {
  name_prefix = "maintenance-cloudfront-invalidation-${var.environment}-"
  description = "Allow GitHub Actions to invalidate CloudFront cache for maintenance page"
  policy      = data.aws_iam_policy_document.maintenance_cloudfront_invalidation.json
}

# S3 policy for maintenance bucket access
data "aws_iam_policy_document" "maintenance_s3_access" {
  statement {
    sid    = "AllowMaintenanceS3Access"
    effect = "Allow"

    actions = [
      "s3:PutObject",
      "s3:DeleteObject",
      "s3:ListBucket"
    ]

    resources = [
      module.maintenance_bucket.s3_bucket_arn,
      "${module.maintenance_bucket.s3_bucket_arn}/*"
    ]
  }
}

resource "aws_iam_policy" "maintenance_s3_access" {
  name_prefix = "maintenance-s3-access-${var.environment}-"
  description = "Allow GitHub Actions to manage maintenance page assets in S3"
  policy      = data.aws_iam_policy_document.maintenance_s3_access.json
}

# GitHub OIDC provider (reference existing one)
data "aws_iam_openid_connect_provider" "github" {
  arn = "arn:aws:iam::${data.aws_caller_identity.current_account_id.account_id}:oidc-provider/token.actions.githubusercontent.com"
}

# Dedicated GitHub Actions role for maintenance deployments
data "aws_iam_policy_document" "maintenance_github_assume_role" {
  statement {
    effect = "Allow"

    principals {
      type        = "Federated"
      identifiers = [data.aws_iam_openid_connect_provider.github.arn]
    }

    actions = ["sts:AssumeRoleWithWebIdentity"]

    condition {
      test     = "StringEquals"
      variable = "token.actions.githubusercontent.com:aud"
      values   = ["sts.amazonaws.com"]
    }

    condition {
      test     = "StringLike"
      variable = "token.actions.githubusercontent.com:sub"
      values = [
        "repo:dvsa/vol-app:environment:${var.environment}",
        "repo:dvsa/vol-app-maintenance:ref:refs/heads/main"
      ]
    }
  }
}

resource "aws_iam_role" "maintenance_github_actions" {
  name_prefix        = "maintenance-github-actions-${var.environment}-"
  assume_role_policy = data.aws_iam_policy_document.maintenance_github_assume_role.json
  description        = "Role for GitHub Actions to deploy maintenance page assets"
}

resource "aws_iam_role_policy_attachment" "maintenance_s3_access" {
  role       = aws_iam_role.maintenance_github_actions.name
  policy_arn = aws_iam_policy.maintenance_s3_access.arn
}

resource "aws_iam_role_policy_attachment" "maintenance_cloudfront_invalidation" {
  role       = aws_iam_role.maintenance_github_actions.name
  policy_arn = aws_iam_policy.maintenance_cloudfront_invalidation.arn
}

# Route53 record for maintenance domain
module "maintenance_records" {
  source  = "terraform-aws-modules/route53/aws//modules/records"
  version = "~> 4.0"

  zone_id = data.aws_route53_zone.public.zone_id

  records = [
    {
      name = local.maintenance_subdomain
      type = "A"
      alias = {
        name    = module.maintenance_cloudfront.cloudfront_distribution_domain_name
        zone_id = module.maintenance_cloudfront.cloudfront_distribution_hosted_zone_id
      }
    },
  ]
}

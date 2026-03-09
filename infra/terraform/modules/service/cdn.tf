provider "aws" {
  alias = "acm"

  # CloudFront expects ACM resources in us-east-1 region only
  region = "us-east-1"

  # Make it faster by skipping various bits not important.
  skip_metadata_api_check     = true
  skip_region_validation      = true
  skip_credentials_validation = true

  # skip_requesting_account_id should be disabled to generate valid ARN in apigatewayv2_api_execution_arn
  skip_requesting_account_id = false
}

data "aws_route53_zone" "public" {
  name = var.domain_name
}

data "aws_caller_identity" "current_account_id" {}

locals {
  asset_bucket = "${data.aws_caller_identity.current_account_id.account_id}-vol-app-assets"
}
data "aws_s3_bucket" "assets" {
  bucket = local.asset_bucket
}

locals {
  domain_name = data.aws_route53_zone.public.name
  subdomain   = "${var.environment}-cdn"
}

module "acm" {
  source  = "terraform-aws-modules/acm/aws"
  version = "~> 6.3"

  domain_name = "${local.subdomain}.${local.domain_name}"
  zone_id     = data.aws_route53_zone.public.id

  validation_method = "DNS"

  create_route53_records  = false
  validation_record_fqdns = module.route53_records.validation_route53_record_fqdns

  providers = {
    aws = aws.acm
  }
}

module "route53_records" {
  source  = "terraform-aws-modules/acm/aws"
  version = "~> 6.3"

  create_certificate          = false
  create_route53_records_only = true

  validation_method = "DNS"

  distinct_domain_names = module.acm.distinct_domain_names
  zone_id               = data.aws_route53_zone.public.id

  acm_certificate_domain_validation_options = module.acm.acm_certificate_domain_validation_options
}

locals {
  oac_id = "s3_oac_${var.environment}"
}

module "cloudfront" {
  source  = "terraform-aws-modules/cloudfront/aws"
  version = "~> 6.4"

  aliases = ["${local.subdomain}.${local.domain_name}"]

  http_version    = "http2and3"
  is_ipv6_enabled = true

  # `PriceClass_100` is most cost efficient for VOL and covers the main region of the VOL user-base (UK).
  price_class = "PriceClass_100"

  wait_for_deployment = true

  # When you enable additional metrics for a distribution, CloudFront sends up to 8 metrics to CloudWatch in the US East (N. Virginia) Region.
  # This rate is charged only once per month, per metric (up to 8 metrics per distribution).
  create_monitoring_subscription = true

  create_origin_access_control = true
  origin_access_control = {
    (local.oac_id) = {
      description      = "CloudFront ${var.environment} access to S3"
      origin_type      = "s3"
      signing_behavior = "always"
      signing_protocol = "sigv4"
    }
  }

  logging_config = {
    bucket = module.log_bucket.s3_bucket_bucket_domain_name
    prefix = "cloudfront"
  }

  origin = {
    (local.oac_id) = {
      domain_name           = data.aws_s3_bucket.assets.bucket_regional_domain_name
      origin_access_control = local.oac_id
      origin_path           = "/${trimprefix(var.assets_version, "/")}"
    }
  }

  default_cache_behavior = {
    target_origin_id       = local.oac_id
    viewer_protocol_policy = "allow-all"
    allowed_methods        = ["GET", "HEAD", "OPTIONS"]
    cached_methods         = ["GET", "HEAD"]

    use_forwarded_values = false

    cache_policy_name            = "Managed-CachingOptimized"
    origin_request_policy_name   = "Managed-UserAgentRefererHeaders"
    response_headers_policy_name = "Managed-SimpleCORS"
  }

  ordered_cache_behavior = [
    {
      path_pattern           = "/*"
      target_origin_id       = local.oac_id
      viewer_protocol_policy = "redirect-to-https"

      allowed_methods = ["GET", "HEAD", "OPTIONS"]
      cached_methods  = ["GET", "HEAD"]

      use_forwarded_values = false

      cache_policy_name            = "Managed-CachingOptimized"
      origin_request_policy_name   = "Managed-UserAgentRefererHeaders"
      response_headers_policy_name = "Managed-CORS-with-preflight-and-SecurityHeadersPolicy"

      function_association = {
        viewer-request = {
          function_arn = aws_cloudfront_function.rewrite_uri.arn
          include_body = true
        }
      }
    }
  ]

  viewer_certificate = {
    acm_certificate_arn = module.acm.acm_certificate_arn
    ssl_support_method  = "sni-only"
  }
}

// The assets are hardcoding in `/static/public/` in the path.
// We need to rewrite the URI to remove it as the new assets doesn't follow the unnecessary directory structure.
// New asset path: `/assets/images/favicon.ico` vs old asset path: `/static/public/assets/images/favicon.ico`.
// This function will provide support for both while EC2 and ECS are running in parallel.
// This can be removed once we remove EC2 infrastructure and the assets no longer have the path prefix.
resource "aws_cloudfront_function" "rewrite_uri" {
  name    = "${var.environment}-legacy-assets-rewrite-uri"
  runtime = "cloudfront-js-2.0"
  publish = true
  code    = <<EOF
function handler(event) {
  var request = event.request;
  request.uri = request.uri.replace(/^\/static\/public\//, "/");
  return request;
}
EOF
}

data "aws_canonical_user_id" "current" {}
data "aws_cloudfront_log_delivery_canonical_user_id" "cloudfront" {}

module "log_bucket" {
  source  = "terraform-aws-modules/s3-bucket/aws"
  version = "~> 5.10"

  bucket = "vol-app-${var.environment}-assets-logs"

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

module "records" {
  source  = "terraform-aws-modules/route53/aws//modules/records"
  version = "~> 6.4"

  zone_id = data.aws_route53_zone.public.zone_id

  records = [
    {
      name = local.subdomain
      type = "A"
      alias = {
        name    = module.cloudfront.cloudfront_distribution_domain_name
        zone_id = module.cloudfront.cloudfront_distribution_hosted_zone_id
      }
    },
  ]
}

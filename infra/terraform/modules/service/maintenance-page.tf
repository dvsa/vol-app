# Maintenance Page Module - Clean Conditional Deployment
module "maintenance_page" {
  count = var.maintenance_page_enabled ? 1 : 0

  source = "../maintenance-page"

  environment     = var.environment
  domain_name     = var.domain_name
  route53_zone_id = data.aws_route53_zone.public.zone_id

  providers = {
    aws.acm = aws.acm
  }
}

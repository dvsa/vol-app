output "maintenance_page_bucket_name" {
  description = "Name of the S3 bucket used for maintenance page hosting"
  value       = module.maintenance_bucket.s3_bucket_id
}

output "maintenance_page_cloudfront_domain" {
  description = "CloudFront distribution domain name for the maintenance page"
  value       = module.maintenance_cloudfront.cloudfront_distribution_domain_name
}

output "maintenance_page_url" {
  description = "The full URL for the maintenance page"
  value       = "https://${local.maintenance_domain}"
}

output "maintenance_page_cloudfront_distribution_id" {
  description = "CloudFront distribution ID for cache invalidation"
  value       = module.maintenance_cloudfront.cloudfront_distribution_id
}

output "maintenance_page_github_actions_role_arn" {
  description = "ARN of the dedicated GitHub Actions role for maintenance page deployments"
  value       = aws_iam_role.maintenance_github_actions.arn
}

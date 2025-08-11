output "maintenance_page_bucket_name" {
  description = "Name of the S3 bucket used for maintenance page hosting"
  value       = var.maintenance_page_enabled ? module.maintenance_page[0].bucket_name : null
}

output "maintenance_page_cloudfront_domain" {
  description = "CloudFront distribution domain name for the maintenance page"
  value       = var.maintenance_page_enabled ? module.maintenance_page[0].cloudfront_domain : null
}

output "maintenance_page_url" {
  description = "The full URL for the maintenance page"
  value       = var.maintenance_page_enabled ? module.maintenance_page[0].url : null
}

output "maintenance_page_cloudfront_distribution_id" {
  description = "CloudFront distribution ID for cache invalidation"
  value       = var.maintenance_page_enabled ? module.maintenance_page[0].cloudfront_distribution_id : null
}

output "maintenance_page_github_actions_role_arn" {
  description = "ARN of the dedicated GitHub Actions role for maintenance page deployments"
  value       = var.maintenance_page_enabled ? module.maintenance_page[0].github_actions_role_arn : null
}

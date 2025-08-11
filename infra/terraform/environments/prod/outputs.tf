output "deployed_api_image_tag" {
  value = null_resource.deployed_versions.triggers["deployed_api_image_tag"]
}

output "deployed_internal_image_tag" {
  value = null_resource.deployed_versions.triggers["deployed_internal_image_tag"]
}

output "deployed_selfserve_image_tag" {
  value = null_resource.deployed_versions.triggers["deployed_selfserve_image_tag"]
}

output "deployed_cli_image_tag" {
  value = null_resource.deployed_versions.triggers["deployed_cli_image_tag"]
}

output "deployed_assets_version" {
  value = null_resource.deployed_versions.triggers["deployed_assets_version"]
}

output "maintenance_page_bucket_name" {
  description = "Name of the S3 bucket used for maintenance page hosting"
  value       = module.service.maintenance_page_bucket_name
}

output "maintenance_page_cloudfront_domain" {
  description = "CloudFront distribution domain name for the maintenance page"
  value       = module.service.maintenance_page_cloudfront_domain
}

output "maintenance_page_url" {
  description = "The full URL for the maintenance page"
  value       = module.service.maintenance_page_url
}

output "maintenance_page_cloudfront_distribution_id" {
  description = "CloudFront distribution ID for cache invalidation"
  value       = module.service.maintenance_page_cloudfront_distribution_id
}

output "maintenance_page_github_actions_role_arn" {
  description = "ARN of the dedicated GitHub Actions role for maintenance page deployments"
  value       = module.service.maintenance_page_github_actions_role_arn
}

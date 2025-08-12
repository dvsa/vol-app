output "bucket_name" {
  description = "Name of the S3 bucket used for maintenance page hosting"
  value       = module.maintenance_bucket.s3_bucket_id
}

output "bucket_arn" {
  description = "ARN of the S3 bucket used for maintenance page hosting"
  value       = module.maintenance_bucket.s3_bucket_arn
}

output "cloudfront_domain" {
  description = "CloudFront distribution domain name for the maintenance page"
  value       = module.maintenance_cloudfront.cloudfront_distribution_domain_name
}

output "cloudfront_distribution_id" {
  description = "CloudFront distribution ID for cache invalidation"
  value       = module.maintenance_cloudfront.cloudfront_distribution_id
}

output "cloudfront_distribution_arn" {
  description = "CloudFront distribution ARN"
  value       = module.maintenance_cloudfront.cloudfront_distribution_arn
}

output "url" {
  description = "The full URL for the maintenance page"
  value       = "https://${var.maintenance_domain}"
}

output "github_actions_role_arn" {
  description = "ARN of the dedicated GitHub Actions role for maintenance page deployments"
  value       = aws_iam_role.maintenance_github_actions.arn
}

output "github_actions_role_name" {
  description = "Name of the dedicated GitHub Actions role for maintenance page deployments"
  value       = aws_iam_role.maintenance_github_actions.name
}

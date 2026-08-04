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

output "idp_classification_sm_arn" {
  description = "ARN of the IDP Classification Step Functions state machine"
  value       = module.idp.classification_sm_arn
}

output "idp_extraction_sm_arn" {
  description = "ARN of the IDP Extraction Step Functions state machine"
  value       = module.idp.extraction_sm_arn
}

output "idp_output_bucket_name" {
  description = "Name of the BDA extraction output S3 bucket"
  value       = module.idp.idp_output_bucket_name
}

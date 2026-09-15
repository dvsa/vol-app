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

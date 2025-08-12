output "deployed_api_image_tag" {
  value = null_resource.deployed_versions.triggers["deployed_api_image_tag"]
}

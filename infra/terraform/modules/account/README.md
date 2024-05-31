<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
|------|---------|
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.0.0 |

## Providers

| Name | Version |
|------|---------|
| <a name="provider_aws"></a> [aws](#provider\_aws) | >= 5.0.0 |

## Modules

| Name | Source | Version |
|------|--------|---------|
| <a name="module_assets"></a> [assets](#module\_assets) | terraform-aws-modules/s3-bucket/aws | ~> 4.0 |
| <a name="module_ecr"></a> [ecr](#module\_ecr) | terraform-aws-modules/ecr/aws | ~> 2.2 |
| <a name="module_github"></a> [github](#module\_github) | ../../modules/github | n/a |

## Resources

| Name | Type |
|------|------|
| [aws_signer_signing_profile.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/signer_signing_profile) | resource |

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_create_assets_bucket"></a> [create\_assets\_bucket](#input\_create\_assets\_bucket) | Whether to create the assets bucket. | `bool` | `false` | no |
| <a name="input_create_ecr_resources"></a> [create\_ecr\_resources](#input\_create\_ecr\_resources) | Whether to create the ECR resources. | `bool` | `false` | no |
| <a name="input_create_github_resources"></a> [create\_github\_resources](#input\_create\_github\_resources) | Whether to create the GitHub resources. | `bool` | `false` | no |
| <a name="input_ecr_read_access_arns"></a> [ecr\_read\_access\_arns](#input\_ecr\_read\_access\_arns) | The list of ARNs to attach to the ECR read role. | `list(string)` | `[]` | no |
| <a name="input_ecr_read_write_access_arns"></a> [ecr\_read\_write\_access\_arns](#input\_ecr\_read\_write\_access\_arns) | The list of ARNs to attach to the ECR read-write role. | `list(string)` | `[]` | no |
| <a name="input_github_oidc_readonly_role_policies"></a> [github\_oidc\_readonly\_role\_policies](#input\_github\_oidc\_readonly\_role\_policies) | The map of policies to attach to the OIDC readonly role. | `map(string)` | `{}` | no |
| <a name="input_github_oidc_readonly_subjects"></a> [github\_oidc\_readonly\_subjects](#input\_github\_oidc\_readonly\_subjects) | The list of GitHub subjects to allow in the OIDC readonly role. | `list(string)` | `[]` | no |
| <a name="input_github_oidc_role_policies"></a> [github\_oidc\_role\_policies](#input\_github\_oidc\_role\_policies) | A map of policy names to policy ARNs to attach to the OIDC role. | `map(string)` | `{}` | no |
| <a name="input_github_oidc_subjects"></a> [github\_oidc\_subjects](#input\_github\_oidc\_subjects) | The list of GitHub subjects to allow in the OIDC role. | `list(string)` | `[]` | no |

## Outputs

No outputs.
<!-- END_TF_DOCS -->

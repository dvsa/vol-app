<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
|------|---------|
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.0.0 |

## Providers

No providers.

## Modules

| Name | Source | Version |
|------|--------|---------|
| <a name="module_ecr"></a> [ecr](#module\_ecr) | terraform-aws-modules/ecr/aws | ~> 1.6 |
| <a name="module_github"></a> [github](#module\_github) | ../../modules/github | n/a |

## Resources

No resources.

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_create_github_resources"></a> [create\_github\_resources](#input\_create\_github\_resources) | Whether to create the GitHub resources. | `bool` | `true` | no |
| <a name="input_ecr_read_access_arns"></a> [ecr\_read\_access\_arns](#input\_ecr\_read\_access\_arns) | The list of ARNs to attach to the ECR read role. | `list(string)` | `[]` | no |
| <a name="input_ecr_read_write_access_arns"></a> [ecr\_read\_write\_access\_arns](#input\_ecr\_read\_write\_access\_arns) | The list of ARNs to attach to the ECR read-write role. | `list(string)` | `[]` | no |
| <a name="input_github_oidc_readonly_role_policies"></a> [github\_oidc\_readonly\_role\_policies](#input\_github\_oidc\_readonly\_role\_policies) | The map of policies to attach to the OIDC readonly role. | `map(string)` | `{}` | no |
| <a name="input_github_oidc_role_policies"></a> [github\_oidc\_role\_policies](#input\_github\_oidc\_role\_policies) | A map of policy names to policy ARNs to attach to the OIDC role. | `map(string)` | `{}` | no |

## Outputs

No outputs.
<!-- END_TF_DOCS -->

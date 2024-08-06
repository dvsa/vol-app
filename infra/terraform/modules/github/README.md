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
| <a name="module_iam_github_oidc_provider"></a> [iam\_github\_oidc\_provider](#module\_iam\_github\_oidc\_provider) | terraform-aws-modules/iam/aws//modules/iam-github-oidc-provider | ~> 5.24 |
| <a name="module_iam_github_oidc_readonly_role"></a> [iam\_github\_oidc\_readonly\_role](#module\_iam\_github\_oidc\_readonly\_role) | terraform-aws-modules/iam/aws//modules/iam-github-oidc-role | ~> 5.24 |
| <a name="module_iam_github_oidc_role"></a> [iam\_github\_oidc\_role](#module\_iam\_github\_oidc\_role) | terraform-aws-modules/iam/aws//modules/iam-github-oidc-role | ~> 5.24 |

## Resources

No resources.

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_create_oidc_provider"></a> [create\_oidc\_provider](#input\_create\_oidc\_provider) | Whether to create an OIDC provider. | `bool` | `true` | no |
| <a name="input_create_oidc_readonly_role"></a> [create\_oidc\_readonly\_role](#input\_create\_oidc\_readonly\_role) | Whether to create a readonly OIDC role. This is useful for pull requests. | `bool` | `true` | no |
| <a name="input_create_oidc_role"></a> [create\_oidc\_role](#input\_create\_oidc\_role) | Whether to create an OIDC role. | `bool` | `true` | no |
| <a name="input_oidc_readonly_role_policies"></a> [oidc\_readonly\_role\_policies](#input\_oidc\_readonly\_role\_policies) | The map of policies to attach to the OIDC readonly role. | `map(string)` | `{}` | no |
| <a name="input_oidc_readonly_subjects"></a> [oidc\_readonly\_subjects](#input\_oidc\_readonly\_subjects) | The list of GitHub subjects to allow in the OIDC readonly role. | `list(string)` | `[]` | no |
| <a name="input_oidc_role_permissions_boundary_arn"></a> [oidc\_role\_permissions\_boundary\_arn](#input\_oidc\_role\_permissions\_boundary\_arn) | The ARN of the permissions boundary to use for the role. | `string` | `null` | no |
| <a name="input_oidc_role_policies"></a> [oidc\_role\_policies](#input\_oidc\_role\_policies) | The map of policies to attach to the OIDC role. | `map(string)` | `{}` | no |
| <a name="input_oidc_role_prefix"></a> [oidc\_role\_prefix](#input\_oidc\_role\_prefix) | The prefix to use for the OIDC roles. | `string` | `null` | no |
| <a name="input_oidc_subjects"></a> [oidc\_subjects](#input\_oidc\_subjects) | The list of GitHub subjects to allow in the OIDC role. | `list(string)` | `[]` | no |

## Outputs

| Name | Description |
|------|-------------|
| <a name="output_oidc_readonly_role_arn"></a> [oidc\_readonly\_role\_arn](#output\_oidc\_readonly\_role\_arn) | The ARN of the GitHub Readonly OIDC role |
| <a name="output_oidc_role_arn"></a> [oidc\_role\_arn](#output\_oidc\_role\_arn) | The ARN of the GitHub OIDC role |
<!-- END_TF_DOCS -->

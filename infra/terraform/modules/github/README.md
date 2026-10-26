<!-- BEGIN_TF_DOCS -->

## Requirements

| Name                                                                     | Version  |
| ------------------------------------------------------------------------ | -------- |
| <a name="requirement_terraform"></a> [terraform](#requirement_terraform) | >= 1.0   |
| <a name="requirement_aws"></a> [aws](#requirement_aws)                   | >= 5.0.0 |

## Providers

No providers.

## Modules

| Name                                                                                                                       | Source                                                                                                | Version                                  |
| -------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| <a name="module_iam_github_oidc_provider"></a> [iam_github_oidc_provider](#module_iam_github_oidc_provider)                | git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-provider | f537b20bb4d31f014c1c63a5b4151177aed694e0 |
| <a name="module_iam_github_oidc_readonly_role"></a> [iam_github_oidc_readonly_role](#module_iam_github_oidc_readonly_role) | git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-role     | f537b20bb4d31f014c1c63a5b4151177aed694e0 |
| <a name="module_iam_github_oidc_role"></a> [iam_github_oidc_role](#module_iam_github_oidc_role)                            | git::https://github.com/terraform-aws-modules/terraform-aws-iam.git//modules/iam-github-oidc-role     | f537b20bb4d31f014c1c63a5b4151177aed694e0 |

## Resources

No resources.

## Inputs

| Name                                                                                                                                    | Description                                                               | Type           | Default | Required |
| --------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------- | -------------- | ------- | :------: |
| <a name="input_create_oidc_provider"></a> [create_oidc_provider](#input_create_oidc_provider)                                           | Whether to create an OIDC provider.                                       | `bool`         | `true`  |    no    |
| <a name="input_create_oidc_readonly_role"></a> [create_oidc_readonly_role](#input_create_oidc_readonly_role)                            | Whether to create a readonly OIDC role. This is useful for pull requests. | `bool`         | `true`  |    no    |
| <a name="input_create_oidc_role"></a> [create_oidc_role](#input_create_oidc_role)                                                       | Whether to create an OIDC role.                                           | `bool`         | `true`  |    no    |
| <a name="input_oidc_readonly_role_policies"></a> [oidc_readonly_role_policies](#input_oidc_readonly_role_policies)                      | The map of policies to attach to the OIDC readonly role.                  | `map(string)`  | `{}`    |    no    |
| <a name="input_oidc_readonly_subjects"></a> [oidc_readonly_subjects](#input_oidc_readonly_subjects)                                     | The list of GitHub subjects to allow in the OIDC readonly role.           | `list(string)` | `[]`    |    no    |
| <a name="input_oidc_role_permissions_boundary_arn"></a> [oidc_role_permissions_boundary_arn](#input_oidc_role_permissions_boundary_arn) | The ARN of the permissions boundary to use for the role.                  | `string`       | `null`  |    no    |
| <a name="input_oidc_role_policies"></a> [oidc_role_policies](#input_oidc_role_policies)                                                 | The map of policies to attach to the OIDC role.                           | `map(string)`  | `{}`    |    no    |
| <a name="input_oidc_role_prefix"></a> [oidc_role_prefix](#input_oidc_role_prefix)                                                       | The prefix to use for the OIDC roles.                                     | `string`       | `null`  |    no    |
| <a name="input_oidc_subjects"></a> [oidc_subjects](#input_oidc_subjects)                                                                | The list of GitHub subjects to allow in the OIDC role.                    | `list(string)` | `[]`    |    no    |

## Outputs

| Name                                                                                                  | Description                              |
| ----------------------------------------------------------------------------------------------------- | ---------------------------------------- |
| <a name="output_oidc_readonly_role_arn"></a> [oidc_readonly_role_arn](#output_oidc_readonly_role_arn) | The ARN of the GitHub Readonly OIDC role |
| <a name="output_oidc_role_arn"></a> [oidc_role_arn](#output_oidc_role_arn)                            | The ARN of the GitHub OIDC role          |

<!-- END_TF_DOCS -->

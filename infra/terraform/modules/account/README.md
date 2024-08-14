<!-- BEGIN_TF_DOCS -->

## Requirements

| Name                                                                     | Version  |
| ------------------------------------------------------------------------ | -------- |
| <a name="requirement_terraform"></a> [terraform](#requirement_terraform) | >= 1.0   |
| <a name="requirement_aws"></a> [aws](#requirement_aws)                   | >= 5.0.0 |

## Providers

| Name                                             | Version  |
| ------------------------------------------------ | -------- |
| <a name="provider_aws"></a> [aws](#provider_aws) | >= 5.0.0 |

## Modules

| Name                                                  | Source                              | Version |
| ----------------------------------------------------- | ----------------------------------- | ------- |
| <a name="module_assets"></a> [assets](#module_assets) | terraform-aws-modules/s3-bucket/aws | ~> 4.0  |
| <a name="module_ecr"></a> [ecr](#module_ecr)          | terraform-aws-modules/ecr/aws       | ~> 2.2  |
| <a name="module_github"></a> [github](#module_github) | ../../modules/github                | n/a     |

## Resources

| Name                                                                                                                                    | Type        |
| --------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| [aws_s3_bucket_policy.bucket_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_policy)      | resource    |
| [aws_signer_signing_profile.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/signer_signing_profile)   | resource    |
| [aws_iam_policy_document.s3_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |

## Inputs

| Name                                                                                                                                    | Description                                                      | Type           | Default | Required |
| --------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------- | -------------- | ------- | :------: |
| <a name="input_create_assets_bucket"></a> [create_assets_bucket](#input_create_assets_bucket)                                           | Whether to create the assets bucket.                             | `bool`         | `false` |    no    |
| <a name="input_create_ecr_resources"></a> [create_ecr_resources](#input_create_ecr_resources)                                           | Whether to create the ECR resources.                             | `bool`         | `false` |    no    |
| <a name="input_create_github_resources"></a> [create_github_resources](#input_create_github_resources)                                  | Whether to create the GitHub resources.                          | `bool`         | `false` |    no    |
| <a name="input_ecr_read_access_arns"></a> [ecr_read_access_arns](#input_ecr_read_access_arns)                                           | The list of ARNs to attach to the ECR read role.                 | `list(string)` | `[]`    |    no    |
| <a name="input_ecr_read_write_access_arns"></a> [ecr_read_write_access_arns](#input_ecr_read_write_access_arns)                         | The list of ARNs to attach to the ECR read-write role.           | `list(string)` | `[]`    |    no    |
| <a name="input_github_oidc_readonly_role_policies"></a> [github_oidc_readonly_role_policies](#input_github_oidc_readonly_role_policies) | The map of policies to attach to the OIDC readonly role.         | `map(string)`  | `{}`    |    no    |
| <a name="input_github_oidc_readonly_subjects"></a> [github_oidc_readonly_subjects](#input_github_oidc_readonly_subjects)                | The list of GitHub subjects to allow in the OIDC readonly role.  | `list(string)` | `[]`    |    no    |
| <a name="input_github_oidc_role_policies"></a> [github_oidc_role_policies](#input_github_oidc_role_policies)                            | A map of policy names to policy ARNs to attach to the OIDC role. | `map(string)`  | `{}`    |    no    |
| <a name="input_github_oidc_subjects"></a> [github_oidc_subjects](#input_github_oidc_subjects)                                           | The list of GitHub subjects to allow in the OIDC role.           | `list(string)` | `[]`    |    no    |

## Outputs

No outputs.

<!-- END_TF_DOCS -->

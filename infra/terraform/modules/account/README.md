<!-- BEGIN_TF_DOCS -->

## Requirements

No requirements.

## Providers

No providers.

## Modules

| Name                                                  | Source               | Version |
| ----------------------------------------------------- | -------------------- | ------- |
| <a name="module_github"></a> [github](#module_github) | ../../modules/github | n/a     |

## Resources

No resources.

## Inputs

| Name                                                                                                                                    | Description                                                      | Type          | Default | Required |
| --------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------- | ------------- | ------- | :------: |
| <a name="input_create_github_resources"></a> [create_github_resources](#input_create_github_resources)                                  | Whether to create the GitHub resources.                          | `bool`        | `true`  |    no    |
| <a name="input_github_oidc_readonly_role_policies"></a> [github_oidc_readonly_role_policies](#input_github_oidc_readonly_role_policies) | The map of policies to attach to the OIDC readonly role.         | `map(string)` | `{}`    |    no    |
| <a name="input_github_oidc_role_policies"></a> [github_oidc_role_policies](#input_github_oidc_role_policies)                            | A map of policy names to policy ARNs to attach to the OIDC role. | `map(string)` | `{}`    |    no    |

## Outputs

No outputs.

<!-- END_TF_DOCS -->

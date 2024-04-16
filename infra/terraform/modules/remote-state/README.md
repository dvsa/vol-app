<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
|------|---------|
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.6.0 |

## Providers

| Name | Version |
|------|---------|
| <a name="provider_aws"></a> [aws](#provider\_aws) | >= 5.6.0 |

## Modules

| Name | Source | Version |
|------|--------|---------|
| <a name="module_dynamodb_state_lock_policy"></a> [dynamodb\_state\_lock\_policy](#module\_dynamodb\_state\_lock\_policy) | terraform-aws-modules/iam/aws//modules/iam-policy | ~> 5.28 |
| <a name="module_dynamodb_table"></a> [dynamodb\_table](#module\_dynamodb\_table) | terraform-aws-modules/dynamodb-table/aws | ~> 4.0 |
| <a name="module_s3"></a> [s3](#module\_s3) | terraform-aws-modules/s3-bucket/aws | ~> 4.0 |
| <a name="module_s3_state_policy"></a> [s3\_state\_policy](#module\_s3\_state\_policy) | terraform-aws-modules/iam/aws//modules/iam-policy | ~> 5.28 |

## Resources

| Name | Type |
|------|------|
| [aws_caller_identity.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_create_bucket"></a> [create\_bucket](#input\_create\_bucket) | Whether to create a state bucket or not. | `bool` | `true` | no |
| <a name="input_create_bucket_policy"></a> [create\_bucket\_policy](#input\_create\_bucket\_policy) | Whether to create a policy for the S3 bucket or not. | `bool` | `true` | no |
| <a name="input_create_dynamodb_policy"></a> [create\_dynamodb\_policy](#input\_create\_dynamodb\_policy) | Whether to create a policy for the DynamoDB table or not. | `bool` | `true` | no |
| <a name="input_environment"></a> [environment](#input\_environment) | The environment in which the resources are deployed. This is used to create a unique name for the resources. | `string` | `null` | no |
| <a name="input_identifier"></a> [identifier](#input\_identifier) | The identifier of the resources. This is used to create a unique name for the resources. | `string` | n/a | yes |

## Outputs

| Name | Description |
|------|-------------|
| <a name="output_dynamodb_state_lock_policy_arn"></a> [dynamodb\_state\_lock\_policy\_arn](#output\_dynamodb\_state\_lock\_policy\_arn) | The ARN of the IAM policy that allows DynamoDB access for state locking |
| <a name="output_s3_state_policy_arn"></a> [s3\_state\_policy\_arn](#output\_s3\_state\_policy\_arn) | The ARN of the IAM policy that allows S3 access for state locking |
<!-- END_TF_DOCS -->

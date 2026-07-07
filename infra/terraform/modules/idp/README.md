<!-- BEGIN_TF_DOCS -->
## Requirements

No requirements.

## Providers

| Name | Version |
|------|---------|
| <a name="provider_archive"></a> [archive](#provider\_archive) | n/a |
| <a name="provider_aws"></a> [aws](#provider\_aws) | n/a |

## Modules

No modules.

## Resources

| Name | Type |
|------|------|
| [aws_cloudwatch_event_rule.document_uploaded](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_rule) | resource |
| [aws_cloudwatch_event_target.classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_target) | resource |
| [aws_cloudwatch_log_group.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_cloudwatch_log_group.classify_document](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_iam_role.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role_policy.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_lambda_function.classify_document](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lambda_function) | resource |
| [aws_sfn_state_machine.classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/sfn_state_machine) | resource |
| [archive_file.classify_document](https://registry.terraform.io/providers/hashicorp/archive/latest/docs/data-sources/file) | data source |
| [aws_caller_identity.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |
| [aws_iam_policy_document.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.eventbridge_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.lambda_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.sfn_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_region.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/region) | data source |
| [aws_s3_bucket.documents](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/s3_bucket) | data source |

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_bedrock_region"></a> [bedrock\_region](#input\_bedrock\_region) | AWS region for Bedrock API calls | `string` | `"eu-west-1"` | no |
| <a name="input_bedrock_region_prefix"></a> [bedrock\_region\_prefix](#input\_bedrock\_region\_prefix) | Cross-region inference profile prefix, e.g. 'eu' or 'us' | `string` | `"eu"` | no |
| <a name="input_classification_model_id"></a> [classification\_model\_id](#input\_classification\_model\_id) | Bedrock foundation model ID for document classification. Combined with bedrock\_region\_prefix to form the cross-region inference profile ID. | `string` | `"anthropic.claude-haiku-4-5-20251001-v1:0"` | no |
| <a name="input_documents_bucket_name"></a> [documents\_bucket\_name](#input\_documents\_bucket\_name) | Name of the pre-existing S3 bucket that receives document uploads (e.g. the sabredav bucket managed in vol-terraform). | `string` | n/a | yes |
| <a name="input_documents_key_prefix"></a> [documents\_key\_prefix](#input\_documents\_key\_prefix) | S3 key prefix used as the EventBridge filter. Only Object Created events whose key starts with this prefix will start the Classification SM. The SM itself further narrows to the current year/month dynamically at runtime. | `string` | `"migration/olcs/documents/Application/Financial_Evidence_Digital/"` | no |
| <a name="input_environment"></a> [environment](#input\_environment) | Deployment environment (dev, int, prep, prod) | `string` | n/a | yes |
| <a name="input_lambda_memory_size"></a> [lambda\_memory\_size](#input\_lambda\_memory\_size) | Memory in MB for the classify-document Lambda. 512 MB gives headroom for base64-encoding a multi-MB PDF in memory. | `number` | `512` | no |
| <a name="input_lambda_timeout"></a> [lambda\_timeout](#input\_lambda\_timeout) | Timeout in seconds for the classify-document Lambda | `number` | `60` | no |

## Outputs

| Name | Description |
|------|-------------|
| <a name="output_classification_sm_arn"></a> [classification\_sm\_arn](#output\_classification\_sm\_arn) | ARN of the Classification Step Functions state machine |
| <a name="output_classification_sm_name"></a> [classification\_sm\_name](#output\_classification\_sm\_name) | Name of the Classification Step Functions state machine |
| <a name="output_classify_document_lambda_arn"></a> [classify\_document\_lambda\_arn](#output\_classify\_document\_lambda\_arn) | ARN of the classify-document Lambda |
| <a name="output_documents_key_prefix"></a> [documents\_key\_prefix](#output\_documents\_key\_prefix) | The S3 key prefix the EventBridge rule watches. Needed as the S3 bucket contains many different directories which are redundant to IDP. Useful for confirming the active filter without reading Terraform state. |
<!-- END_TF_DOCS -->
<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
| ---- | ------- |
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_archive"></a> [archive](#requirement\_archive) | >= 2.0.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.0.0 |

## Providers

| Name | Version |
| ---- | ------- |
| <a name="provider_archive"></a> [archive](#provider\_archive) | 2.8.0 |
| <a name="provider_aws"></a> [aws](#provider\_aws) | 6.53.0 |

## Modules

No modules.

## Resources

| Name | Type |
| ---- | ---- |
| [aws_cloudwatch_event_rule.classified](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_rule) | resource |
| [aws_cloudwatch_event_rule.document_uploaded](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_rule) | resource |
| [aws_cloudwatch_event_target.classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_target) | resource |
| [aws_cloudwatch_event_target.extraction](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_event_target) | resource |
| [aws_cloudwatch_log_group.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_cloudwatch_log_group.classify_document](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_cloudwatch_log_group.extraction_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_iam_role.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.eventbridge_invoke_extraction](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role.extraction_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role_policy.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.eventbridge_invoke_extraction](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_iam_role_policy.extraction_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy) | resource |
| [aws_lambda_function.classify_document](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lambda_function) | resource |
| [aws_s3_bucket.idp_output](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket) | resource |
| [aws_s3_bucket_lifecycle_configuration.idp_output](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_lifecycle_configuration) | resource |
| [aws_s3_bucket_public_access_block.idp_output](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_public_access_block) | resource |
| [aws_s3_bucket_server_side_encryption_configuration.idp_output](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_server_side_encryption_configuration) | resource |
| [aws_sfn_state_machine.classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/sfn_state_machine) | resource |
| [aws_sfn_state_machine.extraction](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/sfn_state_machine) | resource |
| [archive_file.classify_document](https://registry.terraform.io/providers/hashicorp/archive/latest/docs/data-sources/file) | data source |
| [aws_caller_identity.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |
| [aws_iam_policy_document.classification_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.classify_document_lambda](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.eventbridge_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.eventbridge_invoke_classification](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.eventbridge_invoke_extraction](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.extraction_sm](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.lambda_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.sfn_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_region.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/region) | data source |
| [aws_s3_bucket.documents](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/s3_bucket) | data source |

## Inputs

| Name | Description | Type | Default | Required |
| ---- | ----------- | ---- | ------- | :------: |
| <a name="input_bda_project_arn"></a> [bda\_project\_arn](#input\_bda\_project\_arn) | ARN of the Bedrock Data Automation project used for bank statement extraction. | `string` | n/a | yes |
| <a name="input_bda_project_stage"></a> [bda\_project\_stage](#input\_bda\_project\_stage) | BDA project stage to invoke. LIVE uses the latest published blueprint version. | `string` | `"LIVE"` | no |
| <a name="input_bedrock_region"></a> [bedrock\_region](#input\_bedrock\_region) | AWS region for Bedrock API calls | `string` | `"eu-west-1"` | no |
| <a name="input_bedrock_region_prefix"></a> [bedrock\_region\_prefix](#input\_bedrock\_region\_prefix) | Cross-region inference profile prefix, e.g. 'eu' or 'us' | `string` | `"eu"` | no |
| <a name="input_classification_confidence_threshold"></a> [classification\_confidence\_threshold](#input\_classification\_confidence\_threshold) | Minimum classification confidence score (0–1) required to trigger extraction. | `number` | `0.75` | no |
| <a name="input_classification_max_bytes"></a> [classification\_max\_bytes](#input\_classification\_max\_bytes) | Maximum document size in bytes for BDA extraction (default 200 MB). | `number` | `209715200` | no |
| <a name="input_classification_max_pages"></a> [classification\_max\_pages](#input\_classification\_max\_pages) | Maximum total page count for a document to be sent to BDA extraction. | `number` | `100` | no |
| <a name="input_classification_model_id"></a> [classification\_model\_id](#input\_classification\_model\_id) | Bedrock foundation model ID for document classification. Combined with bedrock\_region\_prefix to form the cross-region inference profile ID. | `string` | `"anthropic.claude-haiku-4-5-20251001-v1:0"` | no |
| <a name="input_documents_bucket_name"></a> [documents\_bucket\_name](#input\_documents\_bucket\_name) | Name of the pre-existing S3 bucket that receives document uploads (e.g. the sabredav bucket managed in vol-terraform). | `string` | n/a | yes |
| <a name="input_documents_key_prefix"></a> [documents\_key\_prefix](#input\_documents\_key\_prefix) | S3 key prefix used as the EventBridge filter. Only Object Created events whose key starts with this prefix will start the Classification SM. The SM itself further narrows to the current year/month dynamically at runtime. | `string` | `"migration/olcs/documents/Application/Financial_Evidence_Digital/"` | no |
| <a name="input_environment"></a> [environment](#input\_environment) | Deployment environment (dev, int, prep, prod) | `string` | n/a | yes |
| <a name="input_extraction_classifications"></a> [extraction\_classifications](#input\_extraction\_classifications) | Classification labels that are eligible for BDA extraction. | `list(string)` | <pre>[<br/>  "BANK_STATEMENT",<br/>  "TRANSACTION_REPORT"<br/>]</pre> | no |
| <a name="input_lambda_memory_size"></a> [lambda\_memory\_size](#input\_lambda\_memory\_size) | Memory in MB for the classify-document Lambda. 512 MB gives headroom for base64-encoding a multi-MB PDF in memory. | `number` | `512` | no |
| <a name="input_lambda_timeout"></a> [lambda\_timeout](#input\_lambda\_timeout) | Timeout in seconds for the classify-document Lambda | `number` | `60` | no |

## Outputs

| Name | Description |
| ---- | ----------- |
| <a name="output_classification_sm_arn"></a> [classification\_sm\_arn](#output\_classification\_sm\_arn) | ARN of the Classification Step Functions state machine |
| <a name="output_classification_sm_name"></a> [classification\_sm\_name](#output\_classification\_sm\_name) | Name of the Classification Step Functions state machine |
| <a name="output_classify_document_lambda_arn"></a> [classify\_document\_lambda\_arn](#output\_classify\_document\_lambda\_arn) | ARN of the classify-document Lambda |
| <a name="output_documents_key_prefix"></a> [documents\_key\_prefix](#output\_documents\_key\_prefix) | The S3 key prefix the EventBridge rule watches. Needed as the S3 bucket contains many different directories which are redundant to IDP. Useful for confirming the active filter without reading Terraform state. |
| <a name="output_extraction_sm_arn"></a> [extraction\_sm\_arn](#output\_extraction\_sm\_arn) | ARN of the Extraction Step Functions state machine |
| <a name="output_extraction_sm_name"></a> [extraction\_sm\_name](#output\_extraction\_sm\_name) | Name of the Extraction Step Functions state machine |
| <a name="output_idp_output_bucket_arn"></a> [idp\_output\_bucket\_arn](#output\_idp\_output\_bucket\_arn) | ARN of the BDA output S3 bucket |
| <a name="output_idp_output_bucket_name"></a> [idp\_output\_bucket\_name](#output\_idp\_output\_bucket\_name) | Name of the S3 bucket used by BDA to write extraction results |
<!-- END_TF_DOCS -->
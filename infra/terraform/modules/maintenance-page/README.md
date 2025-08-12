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
| <a name="module_maintenance_acm"></a> [maintenance\_acm](#module\_maintenance\_acm) | terraform-aws-modules/acm/aws | ~> 5.0 |
| <a name="module_maintenance_bucket"></a> [maintenance\_bucket](#module\_maintenance\_bucket) | terraform-aws-modules/s3-bucket/aws | ~> 4.0 |
| <a name="module_maintenance_cloudfront"></a> [maintenance\_cloudfront](#module\_maintenance\_cloudfront) | terraform-aws-modules/cloudfront/aws | ~> 3.0 |
| <a name="module_maintenance_log_bucket"></a> [maintenance\_log\_bucket](#module\_maintenance\_log\_bucket) | terraform-aws-modules/s3-bucket/aws | ~> 4.0 |
| <a name="module_maintenance_records"></a> [maintenance\_records](#module\_maintenance\_records) | terraform-aws-modules/route53/aws//modules/records | ~> 4.0 |
| <a name="module_maintenance_route53_records"></a> [maintenance\_route53\_records](#module\_maintenance\_route53\_records) | terraform-aws-modules/acm/aws | ~> 5.0 |

## Resources

| Name | Type |
|------|------|
| [aws_iam_policy.maintenance_cloudfront_invalidation](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_policy) | resource |
| [aws_iam_policy.maintenance_s3_access](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_policy) | resource |
| [aws_iam_role.maintenance_github_actions](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role) | resource |
| [aws_iam_role_policy_attachment.maintenance_cloudfront_invalidation](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy_attachment) | resource |
| [aws_iam_role_policy_attachment.maintenance_s3_access](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/iam_role_policy_attachment) | resource |
| [aws_caller_identity.current_account_id](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |
| [aws_canonical_user_id.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/canonical_user_id) | data source |
| [aws_cloudfront_log_delivery_canonical_user_id.cloudfront](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/cloudfront_log_delivery_canonical_user_id) | data source |
| [aws_iam_openid_connect_provider.github](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_openid_connect_provider) | data source |
| [aws_iam_policy_document.maintenance_bucket_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.maintenance_cloudfront_invalidation](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.maintenance_github_assume_role](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |
| [aws_iam_policy_document.maintenance_s3_access](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document) | data source |

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_github_oidc_subjects"></a> [github\_oidc\_subjects](#input\_github\_oidc\_subjects) | List of GitHub OIDC subjects allowed to assume the maintenance deployment role | `list(string)` | `[]` | no |
| <a name="input_maintenance_domain"></a> [maintenance\_domain](#input\_maintenance\_domain) | The full domain name for the maintenance page (e.g., maintenance.vol-app.test.dvsa.gov.uk) | `string` | n/a | yes |
| <a name="input_route53_zone_id"></a> [route53\_zone\_id](#input\_route53\_zone\_id) | Route53 zone ID for DNS records | `string` | n/a | yes |

## Outputs

| Name | Description |
|------|-------------|
| <a name="output_bucket_arn"></a> [bucket\_arn](#output\_bucket\_arn) | ARN of the S3 bucket used for maintenance page hosting |
| <a name="output_bucket_name"></a> [bucket\_name](#output\_bucket\_name) | Name of the S3 bucket used for maintenance page hosting |
| <a name="output_cloudfront_distribution_arn"></a> [cloudfront\_distribution\_arn](#output\_cloudfront\_distribution\_arn) | CloudFront distribution ARN |
| <a name="output_cloudfront_distribution_id"></a> [cloudfront\_distribution\_id](#output\_cloudfront\_distribution\_id) | CloudFront distribution ID for cache invalidation |
| <a name="output_cloudfront_domain"></a> [cloudfront\_domain](#output\_cloudfront\_domain) | CloudFront distribution domain name for the maintenance page |
| <a name="output_github_actions_role_arn"></a> [github\_actions\_role\_arn](#output\_github\_actions\_role\_arn) | ARN of the dedicated GitHub Actions role for maintenance page deployments |
| <a name="output_github_actions_role_name"></a> [github\_actions\_role\_name](#output\_github\_actions\_role\_name) | Name of the dedicated GitHub Actions role for maintenance page deployments |
| <a name="output_url"></a> [url](#output\_url) | The full URL for the maintenance page |
<!-- END_TF_DOCS -->
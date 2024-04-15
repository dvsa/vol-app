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

| Name                                                                 | Source                                             | Version |
| -------------------------------------------------------------------- | -------------------------------------------------- | ------- |
| <a name="module_cloudfront"></a> [cloudfront](#module_cloudfront)    | terraform-aws-modules/cloudfront/aws               | ~> 3.4  |
| <a name="module_ecs_cluster"></a> [ecs_cluster](#module_ecs_cluster) | terraform-aws-modules/ecs/aws//modules/cluster     | ~> 5.10 |
| <a name="module_ecs_service"></a> [ecs_service](#module_ecs_service) | terraform-aws-modules/ecs/aws//modules/service     | ~> 5.10 |
| <a name="module_log_bucket"></a> [log_bucket](#module_log_bucket)    | terraform-aws-modules/s3-bucket/aws                | ~> 4.0  |
| <a name="module_records"></a> [records](#module_records)             | terraform-aws-modules/route53/aws//modules/records | ~> 2.0  |
| <a name="module_s3_one"></a> [s3_one](#module_s3_one)                | terraform-aws-modules/s3-bucket/aws                | ~> 4.0  |

## Resources

| Name                                                                                                                                                                                 | Type        |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------- |
| [aws_s3_bucket_policy.bucket_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_policy)                                                   | resource    |
| [aws_acm_certificate.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/acm_certificate)                                                           | data source |
| [aws_canonical_user_id.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/canonical_user_id)                                                    | data source |
| [aws_cloudfront_log_delivery_canonical_user_id.cloudfront](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/cloudfront_log_delivery_canonical_user_id) | data source |
| [aws_iam_policy_document.s3_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document)                                              | data source |
| [aws_route53_zone.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/route53_zone)                                                                 | data source |

## Inputs

| Name                                                               | Description                  | Type                                                                                                                                                        | Default | Required |
| ------------------------------------------------------------------ | ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- | :------: |
| <a name="input_environment"></a> [environment](#input_environment) | The environment to deploy to | `string`                                                                                                                                                    | n/a     |   yes    |
| <a name="input_services"></a> [services](#input_services)          | The services to deploy       | <pre>map(object({<br> image = string<br> cpu = number<br> memory = number<br> security_group_ids = list(string)<br> subnet_ids = list(string)<br> }))</pre> | `{}`    |    no    |

## Outputs

No outputs.

<!-- END_TF_DOCS -->

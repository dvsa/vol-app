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

| Name                                                                             | Source                                             | Version |
| -------------------------------------------------------------------------------- | -------------------------------------------------- | ------- |
| <a name="module_acm"></a> [acm](#module_acm)                                     | terraform-aws-modules/acm/aws                      | ~> 4.0  |
| <a name="module_cloudfront"></a> [cloudfront](#module_cloudfront)                | terraform-aws-modules/cloudfront/aws               | ~> 3.4  |
| <a name="module_ecs_cluster"></a> [ecs_cluster](#module_ecs_cluster)             | terraform-aws-modules/ecs/aws//modules/cluster     | ~> 5.10 |
| <a name="module_ecs_service"></a> [ecs_service](#module_ecs_service)             | terraform-aws-modules/ecs/aws//modules/service     | ~> 5.10 |
| <a name="module_efs"></a> [efs](#module_efs)                                     | terraform-aws-modules/efs/aws                      | 1.6     |
| <a name="module_log_bucket"></a> [log_bucket](#module_log_bucket)                | terraform-aws-modules/s3-bucket/aws                | ~> 4.0  |
| <a name="module_records"></a> [records](#module_records)                         | terraform-aws-modules/route53/aws//modules/records | ~> 2.0  |
| <a name="module_route53_records"></a> [route53_records](#module_route53_records) | terraform-aws-modules/acm/aws                      | ~> 4.0  |

## Resources

| Name                                                                                                                                                                                 | Type        |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ----------- |
| [aws_lb_listener_rule.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_listener_rule)                                                            | resource    |
| [aws_lb_target_group.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_target_group)                                                              | resource    |
| [aws_s3_bucket_policy.bucket_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/s3_bucket_policy)                                                   | resource    |
| [aws_canonical_user_id.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/canonical_user_id)                                                    | data source |
| [aws_cloudfront_log_delivery_canonical_user_id.cloudfront](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/cloudfront_log_delivery_canonical_user_id) | data source |
| [aws_iam_policy_document.s3_policy](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/iam_policy_document)                                              | data source |
| [aws_route53_zone.private](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/route53_zone)                                                              | data source |
| [aws_route53_zone.public](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/route53_zone)                                                               | data source |
| [aws_s3_bucket.assets](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/s3_bucket)                                                                     | data source |

## Inputs

| Name                                                                        | Description                         | Type                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           | Default | Required |
| --------------------------------------------------------------------------- | ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------- | :------: |
| <a name="input_assets_version"></a> [assets_version](#input_assets_version) | The version of the assets           | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | n/a     |   yes    |
| <a name="input_domain_name"></a> [domain_name](#input_domain_name)          | The domain name for the environment | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | n/a     |   yes    |
| <a name="input_environment"></a> [environment](#input_environment)          | The environment to deploy to        | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | n/a     |   yes    |
| <a name="input_services"></a> [services](#input_services)                   | The services to deploy              | <pre>map(object({<br> version = string<br> repository = string<br> cpu = number<br> memory = number<br> task_iam_role_statements = list(object({<br> effect = string<br> actions = list(string)<br> resources = list(string)<br> }))<br> add_cdn_url_to_env = optional(bool, false)<br> lb_listener_arn = string<br> listener_rule_priority = optional(number, 10)<br> listener_rule_host_header = optional(string, "\*")<br> security_group_ids = list(string)<br> subnet_ids = list(string)<br> cidr_blocks = list(string)<br> vpc_id = optional(string, null)<br> }))</pre> | `{}`    |    no    |
| <a name="input_vpc_azs"></a> [vpc_azs](#input_vpc_azs)                      | The VPC AZ to deploy to             | `list(string)`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 | n/a     |   yes    |
| <a name="input_vpc_id"></a> [vpc_id](#input_vpc_id)                         | The VPC ID                          | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | n/a     |   yes    |
| <a name="input_vpc_ids"></a> [vpc_ids](#input_vpc_ids)                      | The VPC to deploy to                | `string`                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       | n/a     |   yes    |

## Outputs

No outputs.

<!-- END_TF_DOCS -->

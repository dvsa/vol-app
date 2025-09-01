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
| <a name="module_acm"></a> [acm](#module\_acm) | terraform-aws-modules/acm/aws | ~> 5.0 |
| <a name="module_batch"></a> [batch](#module\_batch) | terraform-aws-modules/batch/aws | ~> 2.0 |
| <a name="module_cloudfront"></a> [cloudfront](#module\_cloudfront) | terraform-aws-modules/cloudfront/aws | ~> 3.4 |
| <a name="module_cloudwatch_log-metric-filter"></a> [cloudwatch\_log-metric-filter](#module\_cloudwatch\_log-metric-filter) | terraform-aws-modules/cloudwatch/aws//modules/log-metric-filter | 5.7.0 |
| <a name="module_ecs_cluster"></a> [ecs\_cluster](#module\_ecs\_cluster) | terraform-aws-modules/ecs/aws//modules/cluster | ~> 5.10 |
| <a name="module_ecs_service"></a> [ecs\_service](#module\_ecs\_service) | terraform-aws-modules/ecs/aws//modules/service | ~> 5.10 |
| <a name="module_eventbridge"></a> [eventbridge](#module\_eventbridge) | terraform-aws-modules/eventbridge/aws | ~> 3.7 |
| <a name="module_eventbridge_sns"></a> [eventbridge\_sns](#module\_eventbridge\_sns) | terraform-aws-modules/eventbridge/aws | ~> 3.7 |
| <a name="module_log_bucket"></a> [log\_bucket](#module\_log\_bucket) | terraform-aws-modules/s3-bucket/aws | ~> 4.0 |
| <a name="module_records"></a> [records](#module\_records) | terraform-aws-modules/route53/aws//modules/records | ~> 4.0 |
| <a name="module_route53_records"></a> [route53\_records](#module\_route53\_records) | terraform-aws-modules/acm/aws | ~> 5.0 |
| <a name="module_sns_batch_failure"></a> [sns\_batch\_failure](#module\_sns\_batch\_failure) | terraform-aws-modules/sns/aws | ~> 6.1 |

## Resources

| Name | Type |
|------|------|
| [aws_cloudfront_function.rewrite_uri](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudfront_function) | resource |
| [aws_cloudwatch_dashboard.services](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_dashboard) | resource |
| [aws_cloudwatch_dashboard.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_dashboard) | resource |
| [aws_cloudwatch_log_group.failures](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_cloudwatch_log_group.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/cloudwatch_log_group) | resource |
| [aws_lb_listener_rule.internal-pub](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_listener_rule) | resource |
| [aws_lb_listener_rule.internal-pub-proving](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_listener_rule) | resource |
| [aws_lb_listener_rule.proving](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_listener_rule) | resource |
| [aws_lb_listener_rule.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_listener_rule) | resource |
| [aws_lb_target_group.internal-pub](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_target_group) | resource |
| [aws_lb_target_group.this](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/resources/lb_target_group) | resource |
| [aws_caller_identity.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |
| [aws_caller_identity.current_account_id](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/caller_identity) | data source |
| [aws_canonical_user_id.current](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/canonical_user_id) | data source |
| [aws_cloudfront_log_delivery_canonical_user_id.cloudfront](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/cloudfront_log_delivery_canonical_user_id) | data source |
| [aws_route53_zone.public](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/route53_zone) | data source |
| [aws_s3_bucket.assets](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/s3_bucket) | data source |
| [aws_secretsmanager_secret.application_api](https://registry.terraform.io/providers/hashicorp/aws/latest/docs/data-sources/secretsmanager_secret) | data source |

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_assets_version"></a> [assets\_version](#input\_assets\_version) | The version of the assets | `string` | n/a | yes |
| <a name="input_batch"></a> [batch](#input\_batch) | Configuration for the batch process | <pre>object({<br/>    cli_version          = string<br/>    cli_repository       = string<br/>    liquibase_repository = string<br/>    api_secret_file      = string<br/>    subnet_ids           = list(string)<br/>    alert_emails         = optional(list(string))<br/>    task_iam_role_statements = list(object({<br/>      effect    = string<br/>      actions   = list(string)<br/>      resources = list(string)<br/>    }))<br/>    jobs = list(object({<br/>      name     = string<br/>      type     = optional(string, "default")<br/>      queue    = optional(string, "default")<br/>      commands = optional(list(string))<br/>      cpu      = optional(number, 1)<br/>      memory   = optional(number, 2048)<br/>      timeout  = optional(number, 300)<br/>      schedule = optional(list(string), [])<br/>    }))<br/>  })</pre> | n/a | yes |
| <a name="input_domain_env"></a> [domain\_env](#input\_domain\_env) | The domain environment to deploy use | `string` | n/a | yes |
| <a name="input_domain_name"></a> [domain\_name](#input\_domain\_name) | The domain name for the environment | `string` | n/a | yes |
| <a name="input_elasticache_url"></a> [elasticache\_url](#input\_elasticache\_url) | The URL of the Elasticache cluster | `string` | n/a | yes |
| <a name="input_environment"></a> [environment](#input\_environment) | The environment to deploy to | `string` | n/a | yes |
| <a name="input_legacy_environment"></a> [legacy\_environment](#input\_legacy\_environment) | The legacy environment to deploy use | `string` | n/a | yes |
| <a name="input_services"></a> [services](#input\_services) | The services to deploy | <pre>map(object({<br/>    version    = string<br/>    repository = string<br/>    cpu        = number<br/>    memory     = number<br/>    task_iam_role_statements = list(object({<br/>      effect    = string<br/>      actions   = list(string)<br/>      resources = list(string)<br/>    }))<br/>    add_cdn_url_to_env          = optional(bool, false)<br/>    enable_autoscaling_policies = optional(bool, true)<br/>    lb_arn                      = optional(string)<br/>    lb_listener_arn             = optional(string)<br/>    iuweb_pub_listener_arn      = optional(string)<br/>    // The reason for this was to enable the parallel running of ECS and EC2 services.<br/>    // This boolean will control the flow of traffic. If `true`, traffic will go to ECS. If `false`, traffic will go to EC2.<br/>    // Can be removed when EC2 services are removed.<br/>    listener_rule_enable              = optional(bool, true)<br/>    listener_rule_priority            = optional(number, 10)<br/>    listener_rule_host_header         = optional(list(string), ["*"])<br/>    listener_rule_host_header_proving = optional(list(string), ["*"])<br/>    security_group_ids                = list(string)<br/>    subnet_ids                        = list(string)<br/>    vpc_id                            = optional(string, null)<br/>  }))</pre> | `{}` | no |
| <a name="input_vpc_id"></a> [vpc\_id](#input\_vpc\_id) | The VPC ID | `string` | n/a | yes |

## Outputs

No outputs.
<!-- END_TF_DOCS -->

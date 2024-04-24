<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
|------|---------|
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.0.0 |

## Providers

No providers.

## Modules

| Name | Source | Version |
|------|--------|---------|
| <a name="module_ecs_cluster"></a> [ecs\_cluster](#module\_ecs\_cluster) | terraform-aws-modules/ecs/aws//modules/cluster | ~> 5.10 |
| <a name="module_ecs_service"></a> [ecs\_service](#module\_ecs\_service) | terraform-aws-modules/ecs/aws//modules/service | ~> 5.10 |
| <a name="module_efs"></a> [efs](#module\_efs) | terraform-aws-modules/efs/aws | 1.6.2 |

## Resources

No resources.

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_efs_prefix"></a> [efs\_prefix](#input\_efs\_prefix) | The prefix assigned to EFS mount | `string` | n/a | yes |
| <a name="input_environment"></a> [environment](#input\_environment) | The environment to deploy to | `string` | n/a | yes |
| <a name="input_services"></a> [services](#input\_services) | The services to deploy | <pre>map(object({<br>    image              = string<br>    cpu                = number<br>    memory             = number<br>    security_group_ids = list(string)<br>    subnet_ids         = list(string)<br>    cidr_blocks        = list(string)<br>  }))</pre> | `{}` | no |
| <a name="input_vpc_azs"></a> [vpc\_azs](#input\_vpc\_azs) | The VPC AZ to deploy to | `list(string)` | n/a | yes |
| <a name="input_vpc_ids"></a> [vpc\_ids](#input\_vpc\_ids) | The VPC to deploy to | `string` | n/a | yes |

## Outputs

No outputs.
<!-- END_TF_DOCS -->

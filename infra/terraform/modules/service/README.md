<!-- BEGIN_TF_DOCS -->

## Requirements

| Name                                                                     | Version  |
| ------------------------------------------------------------------------ | -------- |
| <a name="requirement_terraform"></a> [terraform](#requirement_terraform) | >= 1.0   |
| <a name="requirement_aws"></a> [aws](#requirement_aws)                   | >= 5.0.0 |

## Providers

No providers.

## Modules

| Name                                                                 | Source                                         | Version |
| -------------------------------------------------------------------- | ---------------------------------------------- | ------- |
| <a name="module_ecs_cluster"></a> [ecs_cluster](#module_ecs_cluster) | terraform-aws-modules/ecs/aws//modules/cluster | ~> 5.10 |
| <a name="module_ecs_service"></a> [ecs_service](#module_ecs_service) | terraform-aws-modules/ecs/aws//modules/service | ~> 5.10 |

## Resources

No resources.

## Inputs

| Name                                                               | Description                  | Type                                                                                                                                                        | Default | Required |
| ------------------------------------------------------------------ | ---------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- | ------- | :------: |
| <a name="input_environment"></a> [environment](#input_environment) | The environment to deploy to | `string`                                                                                                                                                    | n/a     |   yes    |
| <a name="input_services"></a> [services](#input_services)          | The services to deploy       | <pre>map(object({<br> image = string<br> cpu = number<br> memory = number<br> security_group_ids = list(string)<br> subnet_ids = list(string)<br> }))</pre> | `{}`    |    no    |

## Outputs

No outputs.

<!-- END_TF_DOCS -->

module "ecs_cluster" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/cluster"
  version = "~> 5.10"

  cluster_name = "vol-app-${var.environment}-${each.key}-cluster"

  create_task_exec_iam_role = true

  cluster_settings = [
    {
      "name" : "containerInsights",
      "value" : "enabled"
    }
  ]
}

module "ecs_service" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/service"
  version = "~> 5.10"

  name        = "vol-app-${var.environment}-${each.key}-service"
  cluster_arn = module.ecs_cluster[each.key].arn

  task_exec_iam_role_arn = module.ecs_cluster[each.key].task_exec_iam_role_arn

  cpu    = var.services[each.key].cpu
  memory = var.services[each.key].memory

  container_definitions = {
    (each.key) = {
      cpu       = try(var.services[each.key].task_cpu_limit, var.services[each.key].cpu / 2)
      memory    = try(var.services[each.key].task_memory_limit, var.services[each.key].memory / 4)
      essential = true
      image     = var.services[each.key].image
      port_mappings = [
        {
          name          = "http"
          containerPort = 80
          protocol      = "tcp"
        }
      ]

      environment = [
        {
          name  = "ENVIRONMENT_NAME"
          value = var.environment
        }
      ]

      mount_points = [
        {
          sourceVolume  = "vol-app-${var.environment}-${each.key}",
          containerPath = "/data/cache"
        }
      ]

      readonly_root_filesystem = false

      memory_reservation = 100

      volume = {
        vol-app-efs = {
          name = "vol-app-${var.environment}-${each.key}",
          efs_volume_configuration = {
            file_system_id     = module.efs[each.key].id
            root_directory     = ""
            transit_encryption = "ENABLED"
            authorization_config = {
              access_point_id = module.efs[each.key].access_points["data_cache"].id
              iam             = "ENABLED"
            }
          }
        }
      }
    }
  }

  create_security_group = false
  security_group_ids    = var.services[each.key].security_group_ids
  subnet_ids            = var.services[each.key].subnet_ids
}

module "efs" {
  for_each = var.services

  source  = "terraform-aws-modules/efs/aws"
  version = "1.6.2"

  name           = "vol-app-${var.environment}-${each.key}-efs"
  creation_token = "vol-app-${var.environment}-${each.key}-token"
  encrypted      = true

  attach_policy                      = true
  bypass_policy_lockout_safety_check = false
  policy_statements = [
    {
      sid = "vol-app-${var.environment}-${each.key}-policy"
      actions = [
        "elasticfilesystem:ClientMount",
        "elasticfilesystem:ClientWrite",
      ]
      principals = [
        {
          type        = "Service"
          identifiers = ["ecs-tasks.amazonaws.com"]
        }
      ]
    }
  ]

  mount_targets              = { for k, v in zipmap(var.vpc_azs, var.services[each.key].subnet_ids) : k => { subnet_id = v } }
  security_group_description = "${each.key} EFS security group"
  security_group_vpc_id      = var.vpc_ids
  security_group_rules = {
    vpc = {
      description = "EFS ingress from VPC private subnets"
      cidr_blocks = var.services[each.key].cidr_blocks
    }
  }

  access_points = {
    data_cache = {
      root_directory = {
        path = "/${each.key}/data/cache"
        creation_info = {
          owner_gid   = 98
          owner_uid   = 98
          permissions = "755"
        }
      }
    }
  }

  enable_backup_policy = false

  create_replication_configuration = false

}

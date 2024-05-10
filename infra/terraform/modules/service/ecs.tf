resource "aws_lb_target_group" "this" {
  for_each = var.services

  name        = "vol-app-${var.environment}-${each.key}-tg"
  port        = 8080
  protocol    = "HTTP"
  target_type = "ip"
  vpc_id      = coalesce(each.value.vpc_id, var.vpc_id)

  health_check {
    healthy_threshold   = 2
    unhealthy_threshold = 2
    interval            = 300
    timeout             = 60
    protocol            = "HTTP"
    port                = 8080
    path                = "/healthcheck"
    matcher             = "200-499"
  }
}

resource "aws_lb_listener_rule" "this" {
  for_each = var.services

  listener_arn = each.value.lb_listener_arn
  priority     = each.value.listener_rule_priority

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.this[each.key].arn
  }

  condition {
    host_header {
      values = [each.value.listener_rule_host_header]
    }
  }
}

module "ecs_cluster" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/cluster"
  version = "~> 5.10"

  cluster_name = "vol-app-${var.environment}-${each.key}-cluster"

  create_task_exec_iam_role = true

  cluster_settings = [
    {
      name  = "containerInsights"
      value = "enabled"
    }
  ]
}

module "ecs_service" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/service"
  version = "~> 5.10"

  name        = "vol-app-${var.environment}-${each.key}-service"
  cluster_arn = module.ecs_cluster[each.key].arn

  tasks_iam_role_statements = var.services[each.key].task_iam_role_statements

  enable_execute_command = true

  task_exec_iam_role_arn = module.ecs_cluster[each.key].task_exec_iam_role_arn

  cpu    = var.services[each.key].cpu
  memory = var.services[each.key].memory

  runtime_platform = {
    operating_system_family = "LINUX",
    cpu_architecture        = "ARM64"
  }

  container_definitions = {
    (each.key) = {
      cpu       = try(var.services[each.key].task_cpu_limit, var.services[each.key].cpu / 2)
      memory    = try(var.services[each.key].task_memory_limit, var.services[each.key].memory / 4)
      essential = true
      image     = "${var.services[each.key].repository}:${var.services[each.key].version}"
      port_mappings = [
        {
          name          = "http"
          hostPort      = 8080
          containerPort = 8080
          protocol      = "tcp"
        }
      ]

      # Have to explicitly set the user to null to avoid the default user being set to root.
      user = null

      environment = concat(
        [
          {
            name  = "ENVIRONMENT_NAME"
            value = var.environment
          },
          {
            name  = "APP_VERSION"
            value = var.services[each.key].version
          },
        ],
        each.value.add_cdn_url_to_env ? [
          {
            name  = "CDN_URL"
            value = module.cloudfront.cloudfront_distribution_domain_name
          }
        ] : []
      )

      mount_points = [
        {
          sourceVolume  = "vol-app-${each.key}-efs"
          containerPath = "/data/cache"
        }
      ]

      readonly_root_filesystem = false

      memory_reservation = 100

      volume = {
        vol-app-efs = {
          name = "vol-app-${each.key}-efs"
          efs_volume_configuration = {
            file_system_id     = module.efs[each.key].id
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

  load_balancer = {
    service = {
      target_group_arn = aws_lb_target_group.this[each.key].arn
      container_name   = each.key
      container_port   = 8080
    }
  }

  create_security_group = false
  security_group_ids    = var.services[each.key].security_group_ids
  subnet_ids            = var.services[each.key].subnet_ids
}

module "efs" {
  for_each = var.services

  source  = "terraform-aws-modules/efs/aws"
  version = "1.6"

  name            = "vol-app-${each.key}-efs"
  creation_token  = "vol-app-${each.key}-efs-token"
  encrypted       = true
  throughput_mode = "elastic"

  lifecycle_policy = {
    transition_to_ia                    = "AFTER_7_DAYS"
    transition_to_archive               = "AFTER_30_DAYS"
    transition_to_primary_storage_class = "AFTER_1_ACCESS"
  }

  attach_policy                      = true
  bypass_policy_lockout_safety_check = false
  policy_statements = [
    {
      sid = "vol-app-${each.key}-policy"
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
        path = var.services[each.key].access_point
        creation_info = {
          owner_gid   = 82
          owner_uid   = 82
          permissions = "755"
        }
      }
    }
  }

  enable_backup_policy = false

  create_replication_configuration = false

}

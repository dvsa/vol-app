# Getting api subnets for renderer listener rule conditional
data "aws_subnet" "api_subnets" {
  for_each = toset(concat(
    var.services["api"].subnet_ids,
  ))
  id = each.value
}

data "aws_subnet" "batch_subnets" {
  for_each = toset(concat(
    var.batch.subnet_ids
  ))
  id = each.value
}

locals {
  api_subnets_cidrs = [
    for s in data.aws_subnet.api_subnets : s.cidr_block
  ]
  batch_subnets_cidrs = [
    for s in data.aws_subnet.batch_subnets : s.cidr_block
  ]
}


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

resource "aws_lb_target_group" "internal-pub" {
  count = contains(["prep", "prod"], var.environment) ? 1 : 0

  name        = "vol-app-iuweb-${var.environment}-pub-tg"
  port        = 8080
  protocol    = "HTTP"
  target_type = "ip"
  vpc_id      = var.vpc_id

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
  for_each = {
    for service, config in var.services : service => config
    if config.listener_rule_enable
  }

  listener_arn = each.value.lb_listener_arn
  priority     = each.key == "pdf-converter" ? 86 : each.value.listener_rule_priority

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.this[each.key].arn
  }

  condition {
    host_header {
      values = each.value.listener_rule_host_header
    }
  }
  dynamic "condition" {
    for_each = each.key == "pdf-converter" ? [1] : []
    content {
      source_ip {
        values = local.api_subnets_cidrs
      }
    }
  }
}
resource "aws_lb_listener_rule" "renderer-batch" {
  for_each = {
    for k, v in var.services :
    k => v
    if k == "pdf-converter" && v.listener_rule_enable
  }

  listener_arn = each.value.lb_listener_arn
  priority     = 87

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.this[each.key].arn
  }

  condition {
    host_header {
      values = each.value.listener_rule_host_header
    }
  }
  condition {
    source_ip {
      values = local.batch_subnets_cidrs
    }
  }
}

resource "aws_lb_listener_rule" "proving" {
  for_each = {
    for service, config in var.services : service => config
    if contains(["prep", "prod"], var.environment) && service != "pdf-converter"
  }

  listener_arn = each.value.lb_listener_arn
  priority     = 90

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.this[each.key].arn
  }

  condition {
    host_header {
      values = each.value.listener_rule_host_header_proving
    }
  }
}

resource "aws_lb_listener_rule" "internal-pub-proving" {
  count        = contains(["prep", "prod"], var.environment) ? 1 : 0
  listener_arn = var.services["internal"].iuweb_pub_listener_arn
  priority     = 15

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.internal-pub[0].arn
  }

  condition {
    host_header {
      values = ["proving-iuweb.*"]
    }
  }
}
resource "aws_lb_listener_rule" "internal-pub" {
  for_each = (
    contains(["prep", "prod"], var.environment) && try(var.services["internal"].listener_rule_enable, false)
    ) ? {
    "internal" = var.services["internal"]
  } : {}

  listener_arn = each.value.iuweb_pub_listener_arn
  priority     = 16

  action {
    type             = "forward"
    target_group_arn = aws_lb_target_group.internal-pub[0].arn
  }

  condition {
    host_header {
      values = ["iuweb.*"]
    }
  }
}

module "ecs_cluster" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/cluster"
  version = "~> 7.5.0"

  name = "vol-app-${var.environment}-${each.key}-cluster"

  setting = [
    {
      name  = "containerInsights"
      value = "enabled"
    }
  ]
}

module "ecs_service" {
  for_each = var.services

  source  = "terraform-aws-modules/ecs/aws//modules/service"
  version = "~> 7.5.0"

  disable_v7_default_name_description = true

  name        = "vol-app-${var.environment}-${each.key}-service"
  cluster_arn = module.ecs_cluster[each.key].arn

  tasks_iam_role_statements = each.value.task_iam_role_statements

  enable_execute_command   = true
  task_exec_iam_statements = each.value.task_exec_iam_role_statements

  cpu    = each.value.cpu
  memory = each.value.memory

  autoscaling_min_capacity = try(each.value.autoscaling_min, 1)
  autoscaling_max_capacity = try(each.value.autoscaling_max, 10)
  autoscaling_policies = each.value.enable_autoscaling_policies ? {
    "cpu" = {
      policy_type = "TargetTrackingScaling"
      target_tracking_scaling_policy_configuration = {
        predefined_metric_specification = {
          predefined_metric_type = "ECSServiceAverageCPUUtilization"
        }
      }
    }
    "memory" = {
      policy_type = "TargetTrackingScaling"
      target_tracking_scaling_policy_configuration = {
        predefined_metric_specification = {
          predefined_metric_type = "ECSServiceAverageMemoryUtilization"
        }
      }
    }
  } : {}

  runtime_platform = {
    operating_system_family = "LINUX"
    cpu_architecture        = "ARM64"
  }

  container_definitions = {
    (each.key) = {
      cpu       = try(each.value.task_cpu_limit, each.value.cpu)
      memory    = try(each.value.task_memory_limit, each.value.memory)
      essential = true
      image     = "${each.value.repository}:${each.value.version}"
      portMappings = [
        {
          name          = "http"
          containerPort = 8080
          protocol      = "tcp"
        }
      ]

      user = null

      environment = concat(
        [
          {
            name  = "ENVIRONMENT_NAME"
            value = var.legacy_environment
          },
          {
            name  = "APP_VERSION"
            value = each.value.version
          },
          {
            name  = "ELASTICACHE_URL"
            value = var.elasticache_url
          }
        ],
        each.value.add_cdn_url_to_env ? [
          {
            name  = "CDN_URL"
            value = module.cloudfront.cloudfront_distribution_domain_name
          }
        ] : [],
        each.value.set_custom_port ? [
          {
            name  = "API_PORT"
            value = "8080"
          }
        ] : []
      )

      readonlyRootFilesystem = false
      memoryReservation      = 100
    }
  }

  load_balancer = merge(
    {
      primary = {
        target_group_arn = aws_lb_target_group.this[each.key].arn
        container_name   = each.key
        container_port   = 8080
      }
    },
    each.key == "internal" && contains(["prep", "prod"], var.environment) ? {
      internal_pub = {
        target_group_arn = aws_lb_target_group.internal-pub[0].arn
        container_name   = each.key
        container_port   = 8080
      }
    } : {}
  )

  create_security_group = false
  security_group_ids    = each.value.security_group_ids
  subnet_ids            = each.value.subnet_ids

  wait_for_steady_state = false
  wait_until_stable     = true
  force_new_deployment  = false

  depends_on = [module.ecs_cluster]
}


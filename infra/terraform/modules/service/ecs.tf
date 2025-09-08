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
  priority     = each.value.listener_rule_priority

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

  depends_on = [module.ecs_cluster]

  tasks_iam_role_statements = var.services[each.key].task_iam_role_statements

  enable_execute_command = true

  task_exec_iam_role_arn = module.ecs_cluster[each.key].task_exec_iam_role_arn

  cpu    = var.services[each.key].cpu
  memory = var.services[each.key].memory

  autoscaling_min_capacity = try(var.services[each.key].autoscaling_min, 1)
  autoscaling_max_capacity = try(var.services[each.key].autoscaling_max, 10)
  autoscaling_policies = var.services[each.key].enable_autoscaling_policies ? {
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
    operating_system_family = "LINUX",
    cpu_architecture        = "ARM64"
  }

  container_definitions = {
    (each.key) = {
      cpu       = try(var.services[each.key].task_cpu_limit, var.services[each.key].cpu)
      memory    = try(var.services[each.key].task_memory_limit, var.services[each.key].memory)
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
            value = var.legacy_environment
          },
          {
            name  = "APP_VERSION"
            value = var.services[each.key].version
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

      readonly_root_filesystem = false

      memory_reservation = 100
    }
  }
  load_balancer = concat(
    [
      {
        target_group_arn = aws_lb_target_group.this[each.key].arn
        container_name   = each.key
        container_port   = 8080
      }
    ],
    each.key == "internal" && contains(["prep", "prod"], var.environment) ? [
      {
        target_group_arn = aws_lb_target_group.internal-pub[0].arn
        container_name   = each.key
        container_port   = 8080
      }
    ] : []
  )

  create_security_group = false
  security_group_ids    = var.services[each.key].security_group_ids
  subnet_ids            = var.services[each.key].subnet_ids

  #Altered to false as applies are timing out due to health status not pulling through correctly
  wait_for_steady_state = false
  wait_until_stable     = true
  force_new_deployment  = false


}


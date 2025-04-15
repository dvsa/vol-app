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

  depends_on = [module.ecs_cluster, aws_lb_listener_rule.this]

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
        each.value.add_search_env_info ? local.job_types.search.environment : []
      )

      secrets = each.value.add_search_env_info ? local.job_types.search.secrets : []

      readonly_root_filesystem = false

      memory_reservation = 100
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

  #Altered to false as applies are timing out due to health status not pulling through correctly
  wait_for_steady_state = false
  wait_until_stable     = true
  force_new_deployment  = false
}

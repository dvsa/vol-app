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

      readonly_root_filesystem = false

      memory_reservation = 100
    }
  }

  create_security_group = false
  security_group_ids    = var.services[each.key].security_group_ids
  subnet_ids            = var.services[each.key].subnet_ids
}

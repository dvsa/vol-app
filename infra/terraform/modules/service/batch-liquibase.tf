data "aws_caller_identity" "current" {}

locals {
  default_retry_policy = {
    attempts = 1
    evaluate_on_exit = {
      retry_error = {
        action       = "RETRY"
        on_exit_code = 1
      }
      exit_success = {
        action       = "EXIT"
        on_exit_code = 0
      }
    }
  }
}

module "batch-liquibase" {
  source  = "terraform-aws-modules/batch/aws"
  version = "~> 2.0"

  instance_iam_role_name        = "vol-app-${var.environment}-batch-instance"
  instance_iam_role_description = "Task execution role for vol-app-${var.environment}-batch"

  service_iam_role_name        = "vol-app-${var.environment}-batch-service"
  service_iam_role_description = "Service role for vol-app-${var.environment}-batch"

  compute_environments = {
    fargate = {
      name_prefix = "vol-app-${var.environment}-fargate"

      compute_resources = {
        type               = "FARGATE"
        max_vcpus          = 16
        security_group_ids = var.services["api"]["security_group_ids"]
        subnets            = var.batch-liquibase.subnet_ids
      }
    }
  }

  job_queues = {
    default = {
      name     = "vol-app-${var.environment}-liquibase"
      state    = "ENABLED"
      priority = 1
      tags = {
        JobQueue = "vol-app-${var.environment}-liquibase"
      }
    }
  }

  job_definitions = {
    name                  = "vol-app-${var.environment}-liquibase"
    type                  = "container"
    propagate_tags        = true
    platform_capabilities = ["FARGATE"]

    container_properties = jsonencode({
      command = job.commands

      image = "${var.batch-liquibase.repository}:latest"

      environment = [
        {
          name  = "DB_HOST"
          value = "olcsdb-rds.${var.domain_name}"
        },
        {
          name  = "DB_NAME"
          value = "OLCS_RDS_OLCSDB"
        },
        {
          name  = "DB_USER"
          value = "olcsapi"
        },
        {
          name  = "DB_PORT"
          value = "3306"
        }
      ]

      secrets = [
        {
          name      = "DB_PASSWORD"
          valueFrom = "arn:aws:secretsmanager:eu-west-1:${data.aws_caller_identity.current.account_id}:${var.batch-liquibase.secret_file}:olcs_api_rds_password"
        },
      ]

      runtimePlatform = {
        operatingSystemFamily = "LINUX",
        cpuArchitecture       = "ARM64"
      }

      fargatePlatformConfiguration = {
        platformVersion = "LATEST"
      },

      resourceRequirements = [
        {
          type  = "VCPU",
          value = "1"
        },
        {
          type  = "MEMORY",
          value = "2048"
        },
      ],

      executionRoleArn = module.ecs_service["api"].task_exec_iam_role_arn
      jobRoleArn       = module.ecs_service["api"].tasks_iam_role_arn

      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.this.id
          awslogs-region        = "eu-west-1"
          awslogs-stream-prefix = "vol-app-${var.environment}-liquibase"
        }
      }
    })

    attempt_duration_seconds = 300
    retry_strategy           = local.default_retry_policy
  }
}

resource "aws_cloudwatch_log_group" "this" {
  name              = "/aws/batch/liquibase/${var.environment}"
  retention_in_days = 1
}
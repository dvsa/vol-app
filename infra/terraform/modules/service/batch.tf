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

  jobs = { for job in var.batch.jobs : job.name => {
    name                  = "vol-app-${var.environment}-${job.name}"
    type                  = "container"
    propagate_tags        = true
    platform_capabilities = ["FARGATE"]

    container_properties = jsonencode({
      command = concat([
        "/var/www/html/vendor/bin/laminas",
        "--container=/var/www/html/config/container-cli.php"
      ], job.commands)

      image = "${var.batch.repository}:${var.batch.version}"

      environment = [
        {
          name  = "ENVIRONMENT_NAME"
          value = var.environment
        },
        {
          name  = "APP_VERSION"
          value = var.batch.version
        },
      ],

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
          value = tostring(job.cpu),
        },
        {
          type  = "MEMORY",
          value = tostring(job.memory)
        },
      ],

      executionRoleArn = module.ecs_service["api"].task_exec_iam_role_arn
      jobRoleArn       = module.ecs_service["api"].tasks_iam_role_arn

      logConfiguration = {
        logDriver = "awslogs"
        options = {
          awslogs-group         = aws_cloudwatch_log_group.this.id
          awslogs-region        = "eu-west-1"
          awslogs-stream-prefix = job.name
        }
      }
    })

    attempt_duration_seconds = job.timeout
    retry_strategy           = local.default_retry_policy
  } }

  schedules = { for job in var.batch.jobs : if job.schedule != "" then job.name => {
    description         = "Schedule for ${job.name}"
    schedule_expression = job.schedule
    arn                 = "arn:aws:scheduler:::aws-sdk:batch:submitJob"
    input               = jsonencode({ "jobName" : "${job.name}", "jobQueue" : "vol-app-${var.environment}-default", "jobDefinition" : "arn:aws:batch:eu-west-1:054614622558:job-definition/${job.name}"})
  } }
}

module "batch" {
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
        max_vcpus          = 4
        security_group_ids = var.services["api"]["security_group_ids"]
        subnets            = var.batch.subnet_ids
      }
    }
  }

  job_queues = {
    default = {
      name     = "vol-app-${var.environment}-default"
      state    = "ENABLED"
      priority = 1
    }
  }

  job_definitions = local.jobs
}

module "eventbridge" {
  source = "terraform-aws-modules/eventbridge/aws"

  create_bus  = false
  create_role = true

  schedules = local.schedules
}

resource "aws_cloudwatch_log_group" "this" {
  name              = "/aws/batch/vol-app-${var.environment}"
  retention_in_days = 1
}

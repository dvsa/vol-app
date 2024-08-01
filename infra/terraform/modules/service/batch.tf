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
  create_role = false

  rules = { for job in var.batch.jobs : job.name => {
    description        = "Trigger batch job ${job.name}"
    schedule_expression = job.schedule
    }
  }

  targets = { for job in var.batch.jobs : job.name => [
    {
      # name      = job.name
      # arn       = aws_batch_job_queue.my_job_queue.arn
      # batch_target = {
      #   job_definition  = aws_batch_job_definition.my_job_definition.arn
      #   job_name        = job.name
      #   job_queue       = aws_batch_job_queue.my_job_queue.arn
      # }
      name      = "clean-up-variations"
      arn       = "arn:aws:batch:eu-west-1:054614622558:job-queue/vol-app-dev-default"
      batch_target = {
        job_definition  = "arn:aws:batch:eu-west-1:054614622558:job-definition/vol-app-dev-clean-up-variations"
        job_name        = "vol-app-dev-clean-up-variations"
        job_queue       = "arn:aws:batch:eu-west-1:054614622558:job-queue/vol-app-dev-default"
      }
    }]
  }
}

resource "aws_cloudwatch_log_group" "this" {
  name              = "/aws/batch/vol-app-${var.environment}"
  retention_in_days = 1
}

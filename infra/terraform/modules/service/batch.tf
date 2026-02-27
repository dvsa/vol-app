data "aws_caller_identity" "current" {}

data "aws_secretsmanager_secret" "application_api" {
  name = "${local.account_prefix}${local.env_prefix}-BASE-SM-APPLICATION-API"
}

locals {

  account_prefix = contains(["DEV", "QA"], var.legacy_environment) ? "DEV" : ""
  env_prefix     = var.legacy_environment == "APP" ? "APP" : "APP${var.legacy_environment}"

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

  job_types = {
    default = {
      image = "${var.batch.cli_repository}:${var.batch.cli_version}"

      environment = [
        {
          name  = "ENVIRONMENT_NAME"
          value = var.legacy_environment
        },
        {
          name  = "APP_VERSION"
          value = var.batch.cli_version
        },
      ]

      secrets = []
    }
    liquibase = {
      image = "${var.batch.liquibase_repository}:latest"

      environment = [
        {
          name  = "DB_HOST"
          value = "olcsdb-rds.${var.domain_env}.olcs.${var.domain_name}"
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
          valueFrom = "${data.aws_secretsmanager_secret.application_api.arn}:olcs_api_rds_password::"
        },
      ]
    }

    scripts = {
      image = "${var.batch.cli_repository}:${var.batch.cli_version}"

      environment = [
        {
          name  = "ENVIRONMENT_NAME"
          value = var.legacy_environment
        },
        {
          name  = "FULL_DOMAIN"
          value = "${var.environment}.olcs.${var.domain_name}"
        },
        {
          name  = "DOMAIN"
          value = var.domain_name
        },
        {
          name  = "READDB_HOST"
          value = "olcsreaddb-rds.${var.domain_env}.olcs.${var.domain_name}"
        },
        {
          name  = "READDB_ID"
          value = "${var.environment}-aurora-olcsdb-reader"
        },
        {
          name  = "PROXY"
          value = "proxy.${var.environment}.olcs.${var.domain_name}:3128"
        },
        {
          name  = "APP_VERSION"
          value = var.batch.cli_version
        },
        {
          name  = "READDB_HOST"
          value = "olcsreaddb-rds.${var.environment}.olcs.${var.domain_name}"
        },
      ]

      secrets = [
        {
          name      = "BATCH_DB_PASSWORD"
          valueFrom = "${data.aws_secretsmanager_secret.application_api.arn}:olcs_batch_rds_password::"
        },
        {
          name      = "PRODTODEV_ASSUME_ROLE_ID"
          valueFrom = "${data.aws_secretsmanager_secret.application_api.arn}:nonprod_assume_external_id::"
        },
      ]
    }
  }

  jobs = { for job in var.batch.jobs : job.name => {
    name                  = "vol-app-${var.environment}-${job.name}"
    type                  = "container"
    propagate_tags        = true
    platform_capabilities = ["FARGATE"]

    container_properties = jsonencode({

      command = (job.type == "default" ? concat([
        "/var/www/html/vendor/bin/laminas",
        "--container=/var/www/html/config/container-cli.php",
        "-v"
      ], job.commands) : job.commands)

      image = lookup(local.job_types, job.type, local.job_types.default).image

      environment = lookup(local.job_types, job.type, local.job_types.default).environment

      secrets = lookup(local.job_types, job.type, local.job_types.default).secrets

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
          awslogs-group         = aws_cloudwatch_log_group.this[job.name].id
          awslogs-region        = "eu-west-1"
          awslogs-stream-prefix = job.name
        }
      }
      }
    )

    attempt_duration_seconds = job.timeout
    retry_strategy           = local.default_retry_policy
  } }

  schedules = {
    for pair in flatten([
      for job in var.batch.jobs : [
        for idx, jobschedule in try(job.schedule, []) : {
          job_name = job.name
          schedule = jobschedule
          index    = idx
        }

        if length(try(job.schedule, [])) > 0
      ]
    ]) :
    "${var.environment}-${pair.job_name}-${pair.index}" => {
      description         = "Schedule for ${module.batch.job_definitions[pair.job_name].name}"
      schedule_expression = pair.schedule
      arn                 = "arn:aws:scheduler:::aws-sdk:batch:submitJob"
      input = jsonencode({
        "JobName" : module.batch.job_definitions[pair.job_name].name,
        "JobQueue" : lookup(module.batch.job_queues, lookup(var.batch.jobs[{ for i, j in var.batch.jobs : j.name => i }[pair.job_name]], "queue", "default"), module.batch.job_queues.default).arn,
        "JobDefinition" : module.batch.job_definitions[pair.job_name].arn,
        "ShareIdentifier" : "volapp",
        "SchedulingPriorityOverride" : 1
      })
    }
  }
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
        max_vcpus          = 16
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

      # This doesn't offer much value as a tag, but it's here to avoid: https://github.com/hashicorp/terraform-provider-aws/pull/38636.
      # If the PR is merged, we can remove this.
      tags = {
        JobQueue = "vol-app-${var.environment}-default"
      }
    },
    liquibase = {
      name     = "vol-app-${var.environment}-liquibase"
      state    = "ENABLED"
      priority = 1
      tags = {
        JobQueue = "vol-app-${var.environment}-liquibase"
      }
    },
  }

  job_definitions = local.jobs
}

module "eventbridge" {
  source  = "terraform-aws-modules/eventbridge/aws"
  version = "~> 3.7"

  create_bus = false

  create_role              = true
  role_name                = "vol-app-${var.environment}-batch-scheduler"
  attach_policy_statements = true
  policy_statements = {
    batch = {
      effect = "Allow"
      actions = [
        "batch:SubmitJob"
      ]
      resources = concat(
        [for job in module.batch.job_definitions : job.arn],
        [for job in module.batch.job_queues : job.arn]
      )
    }
  }

  schedules = local.schedules

}

resource "aws_cloudwatch_log_group" "this" {
  for_each = { for job in var.batch.jobs : job.name => job }

  name              = "/aws/batch/vol-app-${var.environment}-${each.value.name}"
  retention_in_days = 1
}

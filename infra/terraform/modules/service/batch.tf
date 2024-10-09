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
          value = var.legacy_environment
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

  schedules = {
    for job in var.batch.jobs : job.name => {
      description         = "Schedule for ${module.batch.job_definitions[job.name].name}"
      schedule_expression = job.schedule
      arn                 = "arn:aws:scheduler:::aws-sdk:batch:submitJob"
      input = jsonencode({
        "JobName" : module.batch.job_definitions[job.name].name,
        "JobQueue" : module.batch.job_queues.default.arn,
        "JobDefinition" : module.batch.job_definitions[job.name].arn,
        "ShareIdentifier" : "volapp",
        "SchedulingPriorityOverride" : 1
      })
    }
    if job.schedule != ""
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
    }
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

module "eventbridge_sns" {
  source  = "terraform-aws-modules/eventbridge/aws"
  version = "~> 3.7"

  create_bus = false

  rules = {
    "vol-app-${var.environment}-batch-failure-event" = {
      description = "Capture failed Batch Events sent to SNS"
      event_pattern = jsonencode({
        "source" : ["aws.batch"],
        "detail-type" : ["Batch Job State Change"],
        "detail" : {
          "status" : [
            "FAILED"
          ],
          "jobName" : [{
            "wildcard" : "vol-app-${var.environment}-*"
          }]
        }
      })
      enabled = true
    }
  }

  targets = {
    "vol-app-${var.environment}-batch-failure-to-sns" = [
      {
        arn = module.sns_batch_failure.topic_arn
      }
    ]
  }

}

module "sns_batch_failure" {
  source  = "terraform-aws-modules/sns/aws"
  version = "~> 6.1"

  name            = "vol-app-${var.environment}-batch-failure-topic"
  use_name_prefix = true

  create_topic_policy         = true
  enable_default_topic_policy = true
  topic_policy_statements = {
    pub = {
      actions = ["sns:Publish"]
      principals = [{
        type = "AWS"
        identifiers = [
          "arn:aws:iam::${data.aws_caller_identity.current.account_id}:root"
        ]
      }]
    },

    sub = {
      actions = [
        "sns:Subscribe",
        "sns:Receive",
      ]

      principals = [{
        type        = "Service"
        identifiers = ["events.amazonaws.com"]
      }]

      conditions = [{
        test     = "ArnLike"
        variable = "aws:SourceArn"
        values   = [module.eventbridge_sns.eventbridge_bus_arn]
      }]
    }
  }

  tags = {
    "Name" = "vol-app-${var.environment}-aws-sns-batch-failure"

  }

}

resource "aws_cloudwatch_log_group" "this" {
  name              = "/aws/batch/vol-app-${var.environment}"
  retention_in_days = 1
}
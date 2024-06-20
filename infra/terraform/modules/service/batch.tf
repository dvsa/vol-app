
module "batch" {
  source = "terraform-aws-modules/batch/aws"

  instance_iam_role_name        = "${var.environment}-batch-test-ecs-instance-role"
  instance_iam_role_path        = "/batch/"
  instance_iam_role_description = "IAM instance role/profile for AWS Batch ECS instance(s)"
  instance_iam_role_tags = {
    ModuleCreatedRole = "Yes"
  }

  service_iam_role_name        = "${var.environment}-batch-test-batch-role"
  service_iam_role_path        = "/batch/"
  service_iam_role_description = "IAM service role for AWS Batch"
  service_iam_role_tags = {
    ModuleCreatedRole = "Yes"
  }

  create_spot_fleet_iam_role      = false
  spot_fleet_iam_role_name        = "${var.environment}-batch-test-spot-role"
  spot_fleet_iam_role_path        = "/batch/"
  spot_fleet_iam_role_description = "IAM spot fleet role for AWS Batch"
  spot_fleet_iam_role_tags = {
    ModuleCreatedRole = "Yes"
  }

  compute_environments = {
    a_fargate = {
      name_prefix = "batch-test-fargate"

      compute_resources = {
        type      = "FARGATE"
        max_vcpus = 4

      security_group_ids    = var.jobs[each.key].security_group_ids
      subnets               = var.jobs[each.key].subnets

        # `tags = {}` here is not applicable for spot
      }
    }

    b_fargate_spot = {
      name_prefix = "batch-test-fargate_spot"

      compute_resources = {
        type      = "FARGATE_SPOT"
        max_vcpus = 4

      security_group_ids    = var.jobs[each.key].security_group_ids
      subnets               = var.jobs[each.key].subnets
        # `tags = {}` here is not applicable for spot
      }
    }
  }

  # Job queus and scheduling policies
  job_queues = {
    low_priority = {
      name     = "BatchTestLowPriorityFargate"
      state    = "ENABLED"
      priority = 1

      tags = {
        JobQueue = "Low priority job queue"
      }
    }

    high_priority = {
      name     = "BatchTestHighPriorityFargate"
      state    = "ENABLED"
      priority = 99

      fair_share_policy = {
        compute_reservation = 1
        share_decay_seconds = 3600

        share_distribution = [{
          share_identifier = "A1*"
          weight_factor    = 0.1
          }, {
          share_identifier = "A2"
          weight_factor    = 0.2
        }]
      }

      tags = {
        JobQueue = "High priority job queue"
      }
    }
  }

  job_definitions = {
    job_configuration = {
      name                  = "batch-test-job"
      propagate_tags        = true
      platform_capabilities = ["FARGATE"]

      container_properties = jsonencode({
        command = "${var.jobs.command}"
        argument = "${var.jobs.argument}"
        image   = "${var.jobs.image}"
        fargatePlatformConfiguration = {
          platformVersion = "LATEST"
        },
        resourceRequirements = [
          { type = "VCPU", value = "1" },
          { type = "MEMORY", value = "${var.jobs.memory}" }
        ],
        executionRoleArn = module.iam_assumable_role_vol_api_task_exec_role.iam_role_arn
        #### CW Log group to be created later
/*        logConfiguration = {
          logDriver = "awslogs"
          options = {
            awslogs-group         = local.vol_api_log_group
            awslogs-region        = var.region
            awslogs-stream-prefix = "ecs"
          }
        }*/
      })

      attempt_duration_seconds = 60
      retry_strategy = {
        attempts = 3
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

      tags = {
        JobDefinition = "BatchTest"
      }
    }
  }

//  tags = local.default_tags
}
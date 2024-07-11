locals {

  jobs = { for job in var.batch.jobs : job.name => {
    name = job.name 
    type = "container"
    propagate_tags        = true
    platform_capabilities = ["FARGATE", ]

    container_properties = jsonencode({
        command = ["/var/www/html/vendor/bin/laminas --container=/var/www/html/config/container-cli.php", var.batch["commands"] ]
        image   = "${"var.batch.repository"}:${"var.batch.version"}"
        fargatePlatformConfiguration = {
          platformVersion = "LATEST"
        },
        resourceRequirements = [
          { type = "VCPU", value = var.batch["jobs"]["cpu"] },
          { type = "MEMORY", value = var.batch["jobs"]["memory"] },
        ],
        executionRoleArn = var.batch["iam_role_arn"]
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
         }}}  
    }
  }
}


module "batch" {
  source  = "terraform-aws-modules/batch/aws"
  version = "~> 2.0.0"

  instance_iam_role_name        = "${var.environment}-batch-app-ecs-instance-role"
  instance_iam_role_path        = "/batch/"
  instance_iam_role_description = "IAM instance role/profile for AWS Batch ECS instance(s)"
  instance_iam_role_tags = {
    ModuleCreatedRole = "Yes"
  }

  service_iam_role_name        = "${var.environment}-batch-app-batch-role"
  service_iam_role_path        = "/batch/"
  service_iam_role_description = "IAM service role for AWS Batch"
  service_iam_role_tags = {
    ModuleCreatedRole = "Yes"
  }

  compute_environments = {
    a_fargate = {
      name_prefix = "batch-app-fargate"

      compute_resources = {
        type      = "FARGATE"
        max_vcpus = 4

        security_group_ids = [var.batch.security_group_ids]
        subnets            = [var.batch.subnet_ids]
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
}

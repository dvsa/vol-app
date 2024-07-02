module "jobs" {
  for_each = var.jobs

  source = "terraform-aws-modules/batch/aws"

  job_definitions = {
    job_configuration = {
      name                  = "batch-test-job"
      type                  = "container"
      propagate_tags        = true
      platform_capabilities = ["FARGATE",]

      container_properties = jsonencode({
        command = each.value["command"],
        image = each.value["image"],
        fargatePlatformConfiguration = {
          platformVersion = "LATEST"
        },
        resourceRequirements = [
          { type = "VCPU", value = "1" },
          { type = "MEMORY", value = each.value["memory"] },
        ],
        jobRoleArn = "arn:aws:iam::054614622558:role/vol-app-dev-api-service-20240418150301367500000003"
        #### CW Log group to be created later
        logConfiguration = {
          logDriver = "awslogs"
          options = {
            awslogs-group         = "/aws/ecs/vol-app-dev-api-cluster"
            awslogs-region        = "eu-west-1"
            awslogs-stream-prefix = "ecs"
          }
        }
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
}
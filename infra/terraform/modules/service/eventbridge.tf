module "eventbridge" {
  source = "terraform-aws-modules/eventbridge/aws"

  bus_name = "api-bus"

  schedules = {
    cli-batch-job = {

      flexible_time_window = {
        mode = "OFF"
      }

      schedule_expression = "rate(1 hours)"

      target = {
        arn      = "arn:aws:scheduler:::aws-sdk:batch:submitJob"
        role_arn = "arn:aws:iam::054614622558:role/batch-execution-role"

        input = jsonencode({
          JobDefinition = "arn:aws:batch:eu-west-1:054614622558:job-definition/mat-test-job:5"
          JobName = "TestJob" 
          JobQueue = "arn:aws:batch:eu-west-1:054614622558:job-queue/MatTestHighPriorityFargate"
        })
      }
    }
  }
}
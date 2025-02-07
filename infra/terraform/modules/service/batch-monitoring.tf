locals {

  dashboard_widgets = concat([

    {
      "height" : 6,
      "width" : 24,
      "y" : 0,
      "x" : 0,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          ["AWS/Events", "TriggeredRules", "RuleName", "vol-app-${var.environment}-batch-failure-event-rule"]
        ],
        "view" : "timeSeries",
        "stacked" : false,
        "region" : "eu-west-1",
        "stat" : "Sum",
        "period" : 300,
        "title" : "Batch failures"
      }
    },
    {
      "type" : "log",
      "x" : 6,
      "y" : 6,
      "width" : 18,
      "height" : 6,
      "properties" : {
        "query" : "SOURCE '/aws/batch/vol-app-${var.environment}-failures' | fields @timestamp, @message, @logStream, @log\n| sort @timestamp desc\n| limit 10000",
        "region" : "eu-west-1",
        "stacked" : false,
        "view" : "table",
        "title" : "Batch failure logs"
      }
    }
    ],
    [
      for job in var.batch.jobs : {
        height = 1
        width  = 6
        y      = 0
        x      = 0
        type   = "log"
        properties = {
          query   = <<-EOT
                SOURCE '/aws/batch/vol-app-${var.environment}-${job.name}' | fields @timestamp, @message, @logStream, @log
                | sort @timestamp desc
                | limit 10000
              EOT
          region  = "eu-west-1"
          stacked = false
          title   = "${job.name}-logs"
          view    = "table"
        }
      }
  ])
}

module "cloudwatch_log-metric-filter" {
  for_each                        = { for job in var.batch.jobs : job.name => job }
  source                          = "terraform-aws-modules/cloudwatch/aws//modules/log-metric-filter"
  version                         = "5.7.0"
  name                            = each.value.name
  pattern                         = "%ERROR%"
  log_group_name                  = "/aws/batch/vol-app-${var.environment}-${each.value.name}"
  metric_transformation_namespace = "vol-app-${var.environment}-batch-errors"
  metric_transformation_value     = "1"
  metric_transformation_name      = each.value.name
}

resource "aws_cloudwatch_dashboard" "this" {
  dashboard_name = "batch-vol-app-${var.environment}"

  dashboard_body = jsonencode({
    widgets = local.dashboard_widgets
  })
}

module "eventbridge_sns" {
  source  = "terraform-aws-modules/eventbridge/aws"
  version = "~> 3.7"

  create_bus  = false
  create_role = true
  role_name   = "vol-app-${var.environment}-batch-failures"

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
    "vol-app-${var.environment}-batch-failure-event" = [
      {
        name = "batch-fail-event"
        arn  = module.sns_batch_failure.topic_arn
      },
      {
        arn  = aws_cloudwatch_log_group.failures.arn
        name = "batch-failures-log-group"
      }
    ]
  }

}

module "sns_batch_failure" {
  source  = "terraform-aws-modules/sns/aws"
  version = "~> 6.1"

  name            = "vol-app-${var.environment}-batch-failure-topic"
  use_name_prefix = true
  display_name    = "vol-app-${var.environment}-batch-event-failed"

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
        "sns:Publish",
        "sns:Subscribe",
        "sns:Receive",
      ]

      principals = [{
        type        = "Service"
        identifiers = ["events.amazonaws.com"]
      }]
    }
  }
  /*
  subscriptions = {
    "vol-app-${var.environment}-batch-failure-email" = {
      protocol = "email"
      endpoint = ""
    }
  */

  tags = {
    "Name" = "vol-app-${var.environment}-aws-sns-batch-failure"

  }

}

resource "aws_cloudwatch_log_group" "failures" {
  name              = "/aws/batch/vol-app-${var.environment}-failures"
  retention_in_days = 1
}
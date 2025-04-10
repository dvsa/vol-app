locals {

  region = "eu-west-1"

  lb_details = {
    for service, details in var.services : service => {
      lb_arn  = details.lb_arn != null && details.lb_arn != "" ? "app/${split("/", details.lb_arn)[2]}/${split("/", details.lb_arn)[3]}" : null
      lb_name = details.lb_arn != null && details.lb_arn != "" ? split("/", details.lb_arn)[2] : null
    } if details.lb_arn != null && details.lb_arn != ""
  }

  dashboard_widgets = [

    {
      "height" : 5,
      "width" : 8,
      "y" : 0,
      "x" : 0,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm1m2 * 100 / mm0m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm1m3 * 100 / mm0m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm1m6 * 100 / mm0m6", "region" : local.region }],
          ["ECS/ContainerInsights", "CpuReserved", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }],
          [".", "CpuUtilized", ".", "vol-app-${var.environment}-internal-cluster", { "id" : "mm1m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm1m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm1m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "CPU Utilization",
        "yAxis" : {
          "left" : {
            "min" : 0,
            "showUnits" : false,
            "label" : "Percent"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Sum"
      }
    },
    {
      "height" : 5,
      "width" : 8,
      "y" : 0,
      "x" : 8,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm1m2 * 100 / mm0m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm1m3 * 100 / mm0m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm1m6 * 100 / mm0m6", "region" : local.region }],
          ["ECS/ContainerInsights", "MemoryReserved", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }],
          [".", "MemoryUtilized", ".", "vol-app-${var.environment}-internal-cluster", { "id" : "mm1m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm1m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm1m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "Memory Utilization",
        "yAxis" : {
          "left" : {
            "min" : 0,
            "showUnits" : false,
            "label" : "Percent"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Sum"
      }
    },
    {
      "height" : 5,
      "width" : 8,
      "y" : 0,
      "x" : 16,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm1m2 * 100 / mm0m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm1m3 * 100 / mm0m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm1m6 * 100 / mm0m6", "region" : local.region }],
          ["ECS/ContainerInsights", "EphemeralStorageReserved", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }],
          [".", "EphemeralStorageUtilized", ".", "vol-app-${var.environment}-internal-cluster", { "id" : "mm1m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm1m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm1m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "Disk Utilization",
        "yAxis" : {
          "left" : {
            "min" : 0,
            "showUnits" : false,
            "label" : "Percent"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Sum"
      }
    },
    {
      "height" : 5,
      "width" : 8,
      "y" : 5,
      "x" : 0,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm0m2 + mm1m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm0m3 + mm1m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm0m6 + mm1m6", "region" : local.region }],
          ["ECS/ContainerInsights", "NetworkRxBytes", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }],
          [".", "NetworkTxBytes", ".", "vol-app-${var.environment}-internal-cluster", { "id" : "mm1m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm1m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm1m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "Network",
        "yAxis" : {
          "left" : {
            "showUnits" : false,
            "label" : "Bytes/Second"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Average"
      }
    },
    {
      "height" : 5,
      "width" : 8,
      "y" : 5,
      "x" : 8,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm0m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm0m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm0m6", "region" : local.region }],
          ["ECS/ContainerInsights", "TaskCount", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "Task Count",
        "yAxis" : {
          "left" : {
            "showUnits" : false,
            "label" : "Count"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Average"
      }
    },
    {
      "height" : 5,
      "width" : 8,
      "y" : 5,
      "x" : 16,
      "type" : "metric",
      "properties" : {
        "metrics" : [
          [{ "id" : "expr1m2", "label" : "vol-app-${var.environment}-internal-cluster", "expression" : "mm0m2", "region" : local.region }],
          [{ "id" : "expr1m3", "label" : "vol-app-${var.environment}-api-cluster", "expression" : "mm0m3", "region" : local.region }],
          [{ "id" : "expr1m6", "label" : "vol-app-${var.environment}-selfserve-cluster", "expression" : "mm0m6", "region" : local.region }],
          ["ECS/ContainerInsights", "ServiceCount", "ClusterName", "vol-app-${var.environment}-internal-cluster", { "id" : "mm0m2", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-api-cluster", { "id" : "mm0m3", "visible" : false, "region" : local.region }],
          ["...", "vol-app-${var.environment}-selfserve-cluster", { "id" : "mm0m6", "visible" : false, "region" : local.region }]
        ],
        "legend" : {
          "position" : "right"
        },
        "title" : "Service Count",
        "yAxis" : {
          "left" : {
            "showUnits" : false,
            "label" : "Count"
          }
        },
        "region" : local.region,
        "liveData" : false,
        "timezone" : "UTC",
        "period" : 300,
        "view" : "timeSeries",
        "stacked" : false,
        "stat" : "Average"
      }
    },
    {
      "type" : "metric",
      "x" : 0,
      "y" : 10,
      "width" : 12,
      "height" : 6,
      "properties" : {
        "view" : "timeSeries",
        "stacked" : false,
        "title" : "ActiveConnectionCount",
        "region" : local.region,
        "metrics" : [for lb in local.lb_details : [
          "AWS/ApplicationELB",
          "ActiveConnectionCount",
          "LoadBalancer",
          lb.lb_arn,
          {
            "label" : lb.lb_name
          }
        ]]
      }
    },
    {
      "type" : "metric",
      "x" : 0,
      "y" : 16,
      "width" : 12,
      "height" : 6,
      "properties" : {
        "view" : "timeSeries",
        "stacked" : false,
        "region" : local.region,
        "title" : "ALB 4XX Count",
        "metrics" : [for lb in local.lb_details : [
          "AWS/ApplicationELB",
          "HTTPCode_ELB_4XX_Count",
          "LoadBalancer",
          lb.lb_arn,
          {
            "label" : lb.lb_name
          }
        ]]
      }
    },
    {
      "type" : "metric",
      "x" : 12,
      "y" : 16,
      "width" : 12,
      "height" : 6,
      "properties" : {
        "view" : "timeSeries",
        "stacked" : false,
        "region" : "eu-west-1",
        "title" : "ALB 5XX Count"
        "metrics" : [for lb in local.lb_details : [
          "AWS/ApplicationELB",
          "HTTPCode_ELB_5XX_Count",
          "LoadBalancer",
          lb.lb_arn,
          {
            "label" : lb.lb_name
          }
        ]]
      }
    },
    {
      "type" : "metric",
      "x" : 12,
      "y" : 10,
      "width" : 12,
      "height" : 6,
      "properties" : {
        "view" : "timeSeries",
        "stacked" : false,
        "region" : "eu-west-1",
        "title" : "RequestCount",
        "metrics" : [for lb in local.lb_details : [
          "AWS/ApplicationELB",
          "RequestCount",
          "LoadBalancer",
          lb.lb_arn,
          {
            "label" : lb.lb_name
          }
        ]]
      }
    }
  ]
}

resource "aws_cloudwatch_dashboard" "services" {
  dashboard_name = "vol-app-${var.environment}"

  dashboard_body = jsonencode({
    widgets = local.dashboard_widgets
  })
}
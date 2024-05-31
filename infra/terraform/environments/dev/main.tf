locals {
  service_names = ["api", "selfserve", "internal"]

  legacy_service_names = ["API", "IUWEB", "SSWEB"]
}

data "aws_ecr_repository" "this" {
  for_each = toset(local.service_names)

  name = "vol-app/${each.key}"
}

data "aws_security_group" "this" {
  for_each = toset(local.legacy_service_names)

  name = "DEV/APP/DEV-OLCS-PRI-${each.key}-SG"
}

data "aws_subnets" "this" {
  for_each = toset(local.legacy_service_names)

  filter {
    name = "tag:Name"
    values = [
      "DEV/APP/DEV-OLCS-PRI-${each.key}-1A",
      "DEV/APP/DEV-OLCS-PRI-${each.key}-1B",
      "DEV/APP/DEV-OLCS-PRI-${each.key}-1C"
    ]
  }
}

data "aws_secretsmanager_secret" "this" {
  for_each = toset(local.service_names)

  name = "DEVAPPDEV-BASE-SM-APPLICATION-${upper(each.key)}"
}

data "aws_cognito_user_pools" "this" {
  name = "DVSA-DEVAPPDEV-COGNITO-USERS"
}

data "aws_lb" "this" {
  for_each = toset(local.legacy_service_names)

  name = "DEVAPPDEV-OLCS-PRI-${(each.key == "API" ? "SVCS" : each.key)}-ALB"
}

data "aws_lb_listener" "this" {
  for_each = toset(local.legacy_service_names)

  load_balancer_arn = data.aws_lb.this[each.key].arn
  port              = each.key == "API" ? 80 : 443
}

data "aws_vpc" "this" {
  filter {
    name = "tag:Name"
    values = [
      "DEV/APP-VPC"
    ]
  }
}

module "service" {
  source = "../../modules/service"

  environment = "dev"

  domain_name    = "dev.olcs.dev-dvsacloud.uk"
  assets_version = var.assets_version

  vpc_id = data.aws_vpc.this.id

  services = {
    "api" = {
      cpu    = 1024
      memory = 4096

      version    = var.api_image_tag
      repository = data.aws_ecr_repository.this["api"].repository_url

      task_iam_role_statements = [
        {
          effect = "Allow"
          actions = [
            "secretsmanager:GetSecretValue"
          ]
          resources = [
            data.aws_secretsmanager_secret.this["api"].arn
          ]
        },
        {
          effect = "Allow"
          actions = [
            "ssm:GetParametersByPath"
          ]
          resources = [
            "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/dev/*"
          ]
        },
        {
          effect = "Allow"
          actions = [
            "sts:AssumeRole"
          ]
          resources = [
            "arn:aws:iam::000081644369:role/txc-int-consumer-role"
          ]
        },
        {
          effect = "Allow"
          actions = [
            "cognito-idp:AdminUpdateUserAttributes",
            "cognito-idp:AdminSetUserPassword",
            "cognito-idp:AdminRespondToAuthChallenge",
            "cognito-idp:AdminResetUserPassword",
            "cognito-idp:AdminInitiateAuth",
            "cognito-idp:AdminGetUser",
            "cognito-idp:AdminEnableUser",
            "cognito-idp:AdminDisableUser",
            "cognito-idp:AdminDeleteUser",
            "cognito-idp:AdminCreateUser",
          ]
          resources = data.aws_cognito_user_pools.this.arns
        },
        {
          effect = "Allow"
          actions = [
            "sqs:SendMessageBatch",
            "sqs:SendMessage",
            "sqs:ReceiveMessage",
            "sqs:PurgeQueue",
            "sqs:ListDeadLetterSourceQueues",
            "sqs:GetQueueAttributes",
            "sqs:DeleteMessageBatch",
            "sqs:DeleteMessage"
          ]
          resources = [
            "arn:aws:sqs:eu-west-1:054614622558:DEVAPPDEV-OLCS-PRI-CHGET-INSOLVENCY-DLQ",
            "arn:aws:sqs:eu-west-1:054614622558:DEVAPPDEV-OLCS-PRI-CHGET-INSOLVENCY",
            "arn:aws:sqs:eu-west-1:054614622558:DEVAPPDEV-OLCS-PRI-CHGET-DLQ",
            "arn:aws:sqs:eu-west-1:054614622558:DEVAPPDEV-OLCS-PRI-CHGET"
          ]
        }
      ]

      subnet_ids = data.aws_subnets.this["API"].ids

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["API"].arn
      listener_rule_host_header = "api.*"
    }

    "internal" = {
      cpu    = 1024
      memory = 4096

      version    = var.internal_image_tag
      repository = data.aws_ecr_repository.this["internal"].repository_url

      add_cdn_url_to_env = true

      task_iam_role_statements = [
        {
          effect = "Allow"
          actions = [
            "secretsmanager:GetSecretValue"
          ]
          resources = [
            data.aws_secretsmanager_secret.this["internal"].arn
          ]
        },
        {
          effect = "Allow"
          actions = [
            "ssm:GetParametersByPath"
          ]
          resources = [
            "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/dev/*"
          ]
        },
      ]

      subnet_ids = data.aws_subnets.this["IUWEB"].ids

      security_group_ids = [
        data.aws_security_group.this["IUWEB"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["IUWEB"].arn
      listener_rule_host_header = "iuweb.*"
    }

    "selfserve" = {
      cpu    = 1024
      memory = 4096

      version    = var.selfserve_image_tag
      repository = data.aws_ecr_repository.this["selfserve"].repository_url

      add_cdn_url_to_env = true

      task_iam_role_statements = [
        {
          effect = "Allow"
          actions = [
            "secretsmanager:GetSecretValue"
          ]
          resources = [
            data.aws_secretsmanager_secret.this["selfserve"].arn
          ]
        },
        {
          effect = "Allow"
          actions = [
            "ssm:GetParametersByPath"
          ]
          resources = [
            "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/dev/*"
          ]
        },
      ]

      subnet_ids = data.aws_subnets.this["SSWEB"].ids

      security_group_ids = [
        data.aws_security_group.this["SSWEB"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["SSWEB"].arn
      listener_rule_host_header = "ssweb.*"
    }
  }
}

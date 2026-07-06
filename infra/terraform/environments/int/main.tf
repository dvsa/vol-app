locals {
  service_names = ["api", "selfserve", "internal", "cli"]

  legacy_service_names = ["API", "IUWEB", "SSWEB", "RENDERER"]

  supporting_service_names = ["liquibase"]

  task_exec_iam_role_statements = [
    {
      effect = "Allow"
      actions = [
        "secretsmanager:GetSecretValue"
      ]
      resources = [
        data.aws_secretsmanager_secret.this["api"].arn,
        data.aws_secretsmanager_secret.infra.arn
      ]
    },
  ]
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
        "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/qa/*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "sts:AssumeRole"
      ]
      resources = [
        "arn:aws:iam::000081644369:role/txc-int-consumer-role",
        "arn:aws:iam::054614622558:role/DBAM-ProdToDev-AssumeRole",
        "arn:aws:iam::054614622558:role/OLCS-DEVAPPCI-DEVCI-Cognito_Pool_Admin"
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
        "arn:aws:sqs:eu-west-1:054614622558:DEVAPPQA-OLCS-PRI-CHGET-INSOLVENCY-DLQ",
        "arn:aws:sqs:eu-west-1:054614622558:DEVAPPQA-OLCS-PRI-CHGET-INSOLVENCY",
        "arn:aws:sqs:eu-west-1:054614622558:DEVAPPQA-OLCS-PRI-CHGET-DLQ",
        "arn:aws:sqs:eu-west-1:054614622558:DEVAPPQA-OLCS-PRI-CHGET"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "s3:PutObject",
      ]
      resources = [
        "arn:aws:s3:::devapp-olcs-pri-olcs-autotest-s3/*",
        "arn:aws:s3:::devapp-vol-content/*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "s3:GetObject",
        "s3:PutObject",
        "s3:ListBucket",
        "s3:DeleteObject"
      ]
      resources = [
        "arn:aws:s3:::devapp-shd-pri-olcsci-build-s3",
        "arn:aws:s3:::devapp-shd-pri-olcsci-build-s3/*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:CreateDBClusterSnapshot",
        "rds:DescribeDBClusterSnapshots",
        "rds:DeleteDBClusterSnapshot",
        "rds:ModifyDBClusterSnapshotAttribute"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:cluster:int-aurora-olcsdb-cluster",
        "arn:aws:rds:eu-west-1:054614622558:cluster:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:ni-extract-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster-snapshot:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster-snapshot:olcs-db-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster-snapshot:ni-extract-*",
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:DescribeDBClusters"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:cluster:int-aurora-olcsdb-cluster",
        "arn:aws:rds:eu-west-1:054614622558:cluster:olcs-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:ni-extract-*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:RestoreDBClusterFromSnapshot",
        "rds:AddTagsToResource"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:cluster-snapshot:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster-snapshot:ni-extract-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:ni-extract-*",
        "arn:aws:rds:eu-west-1:054614622558:subgrp:devappqa-olcs-rds-*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:CreateDBInstance"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:cluster:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:ni-extract-*",
        "arn:aws:rds:eu-west-1:054614622558:db:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:db:ni-extract-*"
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:DescribeDBInstances"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:db:*",
      ]
    },
    {
      effect = "Allow"
      actions = [
        "rds:DeleteDBInstance",
        "rds:DeleteDBCluster"
      ]
      resources = [
        "arn:aws:rds:eu-west-1:054614622558:cluster:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:cluster:ni-extract-*",
        "arn:aws:rds:eu-west-1:054614622558:db:olcs-anon-*",
        "arn:aws:rds:eu-west-1:054614622558:db:ni-extract-*"
      ]
    }

  ]
}

data "aws_ecr_repository" "this" {
  for_each = toset(local.service_names)

  name = "vol-app/${each.key}"
}

data "aws_ecr_repository" "sservice" {
  for_each = toset(local.supporting_service_names)

  name = "vol-app/${each.key}"
}

data "aws_ecr_repository" "gotenberg" {
  name = "vol-app/gotenberg"
}

data "aws_security_group" "this" {
  for_each = toset(local.legacy_service_names)

  name = "DEV/APP/QA-OLCS-PRI-${each.key}-SG"
}

data "aws_subnets" "this" {
  for_each = toset(setunion(local.legacy_service_names, ["BATCH"]))

  filter {
    name = "tag:Name"
    values = [
      "DEV/APP/QA-OLCS-PRI-${each.key}-1A",
      "DEV/APP/QA-OLCS-PRI-${each.key}-1B",
      "DEV/APP/QA-OLCS-PRI-${each.key}-1C"
    ]
  }
}

data "aws_secretsmanager_secret" "this" {
  for_each = toset(setsubtract(local.service_names, ["cli"]))

  name = "DEVAPPQA-BASE-SM-APPLICATION-${upper(each.key)}"
}

data "aws_secretsmanager_secret" "infra" {
  name = "DEVAPPQA-BASE-SM-INFRA"
}

data "aws_cognito_user_pools" "this" {
  name = "DVSA-DEVAPPQA-COGNITO-USERS"
}

data "aws_lb" "this" {
  for_each = setsubtract(local.legacy_service_names, ["RENDERER"])

  name = "DEVAPPQA-OLCS-PRI-${(each.key == "API" ? "SVCS" : each.key)}-ALB"
}

data "aws_lb_listener" "this" {
  for_each = setsubtract(local.legacy_service_names, ["RENDERER"])

  load_balancer_arn = data.aws_lb.this[each.key].arn
  port              = each.key == "API" ? 80 : 443
}

data "aws_lb_listener" "renderer" {

  load_balancer_arn = data.aws_lb.this["API"].arn
  port              = 443
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

  environment = "int"

  legacy_environment = "QA"

  domain_env = "qa"

  domain_name    = "dev-dvsacloud.uk"
  assets_version = var.assets_version

  vpc_id = data.aws_vpc.this.id

  elasticache_url = "tcp://cache.qa.olcs.dev-dvsacloud.uk:6379"

  application_parameters = {
    # api
    domain                                       = "int.olcs.dvsacloud.uk"
    shd_proxy                                    = "proxy.int.olcs.dvsacloud.uk:3128"
    olcs_webdav                                  = "http://webdav.int.olcs.dvsacloud.uk:8080/documents/"
    olcs_aws_sqs_ch_get_queue                    = "APPNDUINT-OLCS-PRI-CHGET"
    olcs_aws_sqs_ch_get_dlq                      = "APPNDUINT-OLCS-PRI-CHGET-DLQ"
    olcs_aws_sqs_ch_insolvency_queue             = "APPNDUINT-OLCS-PRI-CHGET-INSOLVENCY"
    olcs_aws_sqs_ch_insolvency_dlq               = "APPNDUINT-OLCS-PRI-CHGET-INSOLVENCY-DLQ"
    olcs_cpmsserver                              = "api.accept.dev.cpms.dvsacloud.uk"
    olcs_send_all_mail_to                        = "olcs-int@dvsa.gov.uk"
    olcs_from_email                              = "notifications@integration.vehicleoperatorlicensing.service.olcscloud.uk"
    olcs_ss_uri                                  = "https://ssweb.int.olcs.dvsacloud.uk"
    olcs_iu_uri                                  = "https://iuweb.int.olcs.dvsacloud.uk"
    olcs_aws_s3_role_arn                         = "arn:aws:iam::146997448015:role/OLCS-APPNDUINT-BASE-API"
    olcs_notify_template_en_gb                   = " "
    olcs_notify_template_cy_gb                   = " "
    olcs_notify_test_dsn                         = "govuknotify+mailpit://selenium-mail.olcs.dev-dvsacloud.uk:1025"
    olcs_mail_dsn                                = "smtp://selenium-mail.olcs.dev-dvsacloud.uk:1025"
    transxchange_aws_consumer_role               = "arn:aws:iam::259405524870:role/txc-prep-consumer-role"
    transxchange_aws_sqs_output_uri              = "https://sqs.eu-west-1.amazonaws.com/259405524870/txc-prep-output"
    transxchange_aws_s3_output_bucket            = "txc-prep-output"
    redis_cache_fqdn                             = "cache.int.olcs.dvsacloud.uk"
    olcs_dvla_search_base_uri                    = "https://api.dvla.int.smc.dvsacloud.uk/1.0/"
    aws_cognito_region                           = "eu-west-1"
    lar_base_uri                                 = "https://huxwmpie60.execute-api.eu-west-1.amazonaws.com/prep/"
    govuk_account_discovery_endpoint             = "https://oidc.integration.account.gov.uk/.well-known/openid-configuration"
    govuk_account_client_id                      = "FvvrWr8GHF5hbq1O3pWGWirjuCQ"
    govuk_account_private_key_algorithm          = "RS256"
    govuk_account_public_key                     = "LS0tLS1CRUdJTiBQVUJMSUMgS0VZLS0tLS0KTUlJQ0lqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FnOEFNSUlDQ2dLQ0FnRUFyY0Y3TERaRUtJV0RoYUVMWlZBYwpLZW9keWdrQ1h6M0RpajVSRXI1WjRWOSs2QU1td21sMmJ3UTFTK2RCeFBHMmowbndNVHBJd1h3ZGRBNmRlNUh2CnNzMy9SbFdIamVndklzakxEYzhJWFRHcEtqZDU4cDdnZHpOTy9jTE8rUkg1R29zdzRRVVpObXAwRVluWW9PNm8Kd1NXMmYwbVkyQXFYV2x0dnBHckt1eDZZdVlJVnZSdlU5RnVYNkYzWi9ZVjVBYzcwK0s3aFdtWXIyUjBOYlVNaQpYNk9zNzk3SDUyQmc5NGRBRVJVQkR3VGtQbGd3cnZKUGtUWHdLWGc5dGIyTG0zcGowS1BRQk1YRElOaEJSRVNECkt5cEh0SndBQkFkVlVGbmFjaDdiN1kzYm1QNHNjMERMTG84S3pLdmZRdkcvbDJ5OGhzT1VlZEFLMDhIQUNWTXkKQ3JHNW9FZTh4VGJKczJIUDMyL1NtMXZWekt2ZDB1TVF0L2pkbC9kRWlIUURFdENVVmRjOG1xTmtsQUFyM0krTAo3RDhDa2IwbjdMbGJzMmo4YkRBbDlMOGNSYTlhdmd2TGhTSVF1V2s1Vkx5bjdvaWpDSnFaZGFreGhLREhYOHJ0Ck1nYUlnM0h4UFBWNEdrVHVGNkZSOFpwMTNjSzZRWEdsaEg2Vy80ZUs3ZWVqLzdsOE1Dc25rWjExcWNYVVREVjcKdXN4dFI4ZzArNXpJVXlwNlRrbDZYQk95dTlmbEo2TlI4Tk0zYXdQRi9mWkFHUHFlVDFkdjJ5Mk5KQ2FsYWhzTQpXN3hYdmdxZ2hlbGkwZEc1ZG00OGsrVG1mb1N6STRUODY1VnB1Nnk0YmFic0FJSHkrWTd1UkhOcFp4Uis1Ry9WCldkL0lLTGdGN3hVcGRuaXhJZWYzcm1rQ0F3RUFBUT09Ci0tLS0tRU5EIFBVQkxJQyBLRVktLS0tLQo="
    govuk_account_id_assurance_public_key        = "{\"kty\":\"EC\",\"use\":\"sig\",\"crv\":\"P-256\",\"x\":\"qcXlI51yGk-6mzoeudKTqyxu4ORnPlMOYq5R6ZPrdy4\",\"y\":\"L8aFgNjcdDWjhnJSI3F4y0RHfh5KnJMQf37_A_N4ALQ\",\"alg\":\"ES256\"}"
    govuk_account_id_assurance_issuer            = "https://identity.integration.account.gov.uk/"
    govuk_account_core_identity_did_document_url = "https://identity.integration.account.gov.uk/.well-known/did.json"
    operator_reports_api_url                     = "https://operator-reports-api.integration.edh.dvsacloud.uk/redirect"
    address_service_url                          = "https://int.nonprod.address.dvsa.api.gov.uk"
    address_service_azure_token_scope            = "api://a759978f-1ce5-4e46-8980-4f51bed98e45/.default"
    address_service_azure_client_id              = "a759978f-1ce5-4e46-8980-4f51bed98e45"
    address_service_azure_token_url              = "https://login.microsoftonline.com/6c448d90-4ca1-4caf-ab59-0a2aa67d7801/oauth2/v2.0/token"
    olcs_txc_client_id                           = "f917e2d8-ca3a-444e-9328-bb6491447b80"
    olcs_txc_scope                               = "api://f917e2d8-ca3a-444e-9328-bb6491447b80/.default"
    olcs_txc_token_url                           = "https://login.microsoftonline.com/a455b827-244f-4c97-b5b4-ce5d13b4d00c/oauth2/v2.0/token"
    transxchange_uri                             = "https://prep.transxchange.dvsa.api.gov.uk/pdf-request"
    transxchange_aws_s3_input_bucket             = "txc-prep-input"
    data-gov-uk-export-s3uri                     = "s3://app-vol-content/olcs.int.prod.dvsa.aws/data-gov-uk-export"
    data-dva-ni-export-s3uri                     = "s3://appnduint-olcs-pri-integration-dva-s3/dvaoplic/"
    olcs_natreg_client_id                        = "f7dfe3f7-9d46-4fab-a113-7083d3c86a39"
    olcs_natreg_token_url                        = "https://login.microsoftonline.com/6c448d90-4ca1-4caf-ab59-0a2aa67d7801/oauth2/v2.0/token"
    olcs_natreg_client_scope                     = "api://f7dfe3f7-9d46-4fab-a113-7083d3c86a39/.default"

    # internal/external
    env                           = "int"
    olcs_iu_cookie                = "int.olcs.dvsacloud.uk"
    olcs_ss_cookie                = "ssweb.int.olcs.dvsacloud.uk"
    olcs_google_gtm_auth          = "_fwTINmd_OvRhQErzdvjAQ"
    olcs_google_gtm_preview       = "env-462"
    verify_forwarder_valid_origin = "www.integration.signin.service.gov.uk"
    assets_cache_busting_strategy = "release"

    /**
    * RFC: http://tools.ietf.org/html/rfc3164
    * 
    *    Code      Severity
    *      0       Emergency: system is unusable
    *      1       Alert: action must be taken immediately
    *      2       Critical: critical conditions
    *      3       Error: error conditions
    *      4       Warning: warning conditions
    *      5       Notice: normal but significant condition
    *      6       Informational: informational messages
    *      7       Debug: debug-level messages
    */
    log_level = "4"
  }

  services = {
    "api" = {
      cpu             = 2048
      memory          = 4096
      autoscaling_min = 1

      version    = var.api_image_tag
      repository = data.aws_ecr_repository.this["api"].repository_url

      task_iam_role_statements = local.task_iam_role_statements

      task_exec_iam_role_statements = local.task_exec_iam_role_statements

      subnet_ids = data.aws_subnets.this["API"].ids

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["API"].arn
      lb_arn                    = data.aws_lb.this["API"].arn
      listener_rule_host_header = ["api.*"]
    }

    "internal" = {
      cpu             = 2048
      memory          = 4096
      autoscaling_min = 1

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
            "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/qa/*"
          ]
        },
      ]

      subnet_ids = data.aws_subnets.this["IUWEB"].ids

      security_group_ids = [
        data.aws_security_group.this["IUWEB"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["IUWEB"].arn
      lb_arn                    = data.aws_lb.this["IUWEB"].arn
      listener_rule_host_header = ["iuweb.*"]
    }

    "selfserve" = {
      cpu             = 2048
      memory          = 4096
      autoscaling_min = 1

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
            "arn:aws:ssm:eu-west-1:054614622558:parameter/applicationparams/qa/*"
          ]
        },
      ]

      subnet_ids = data.aws_subnets.this["SSWEB"].ids

      security_group_ids = [
        data.aws_security_group.this["SSWEB"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["SSWEB"].arn
      lb_arn                    = data.aws_lb.this["SSWEB"].arn
      listener_rule_host_header = ["ssweb.*"]
    },
    "pdf-converter" = {
      cpu    = 1024
      memory = 2048

      enable_autoscaling_policies = true

      version    = "latest"
      repository = data.aws_ecr_repository.gotenberg.repository_url

      set_custom_port = true

      listener_rule_enable = true

      task_iam_role_statements = []

      subnet_ids = data.aws_subnets.this["RENDERER"].ids

      security_group_ids = [
        data.aws_security_group.this["RENDERER"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.renderer.arn
      lb_arn                    = data.aws_lb.this["API"].arn
      listener_rule_host_header = ["renderer.*"]
      listener_rule_priority    = 5
    }
  }
  batch = {

    cli_version = var.cli_image_tag

    cli_repository       = data.aws_ecr_repository.this["cli"].repository_url
    liquibase_repository = data.aws_ecr_repository.sservice["liquibase"].repository_url
    api_secret_file      = data.aws_secretsmanager_secret.this["api"].arn

    task_iam_role_statements = local.task_iam_role_statements

    subnet_ids = data.aws_subnets.this["BATCH"].ids

    alert_emails = [
      "olcs-dev@otc.gov.uk"
    ]

    jobs = [
      {
        name     = "cache-clear",
        commands = ["batch:cache-clear", "--flush-all", "--force"],
        timeout  = 300,
      },
      {
        name     = "ch-vs-olcs-diffs",
        commands = ["batch:ch-vs-olcs-diffs"],
      },
      {
        name     = "clean-up-variations",
        commands = ["batch:clean-up-variations"],
        timeout  = 43200,
        schedule = ["cron(00 13 * * ? *)"],
      },
      {
        name     = "cns",
        commands = ["batch:cns"],
        timeout  = 43200,
        schedule = ["cron(30 13 ? * 1 *)"],
      },
      {
        name     = "create-psv-licence-surrender-task",
        commands = ["batch:create-psv-licence-surrender-task"],
        timeout  = 43200,
        schedule = ["cron(30 18 7 * ? *)"],
      },
      {
        name     = "psv-operator-list-export",
        commands = ["batch:data-gov-uk-export", "--report-name", "psv-operator-list"],
        timeout  = 43200,
        schedule = ["cron(00 13 ? * 1 *)"],
      },
      {
        name     = "international-goods-export",
        commands = ["batch:data-gov-uk-export", "--report-name", "international-goods"],
        timeout  = 43200,
        schedule = ["cron(00 13 ? * 1 *)"],
      },
      {
        name     = "data-retention-populate",
        commands = ["batch:data-retention", "--populate"],
        timeout  = 7200
      },
      {
        name     = "data-retention-precheck",
        commands = ["batch:data-retention", "--precheck"],
        timeout  = 7200
      },
      {
        name     = "data-retention-delete",
        commands = ["batch:data-retention", "--delete"],
        timeout  = 7200
      },
      {
        name     = "data-retention-postcheck",
        commands = ["batch:data-retention", "--postcheck"],
        timeout  = 7200
      },
      {
        name     = "database-maintenance",
        commands = ["batch:database-maintenance"],
      },
      {
        name     = "digital-continuation-reminders",
        commands = ["batch:digital-continuation-reminders"],
        timeout  = 43200,
        schedule = ["cron(00 13 * * ? *)"],
      },
      {
        name     = "duplicate-vehicle-warning",
        commands = ["batch:duplicate-vehicle-warning"],
        timeout  = 43200,
        schedule = ["cron(30 13 ? * 2-6 *)"],
      },
      {
        name     = "duplicate-vehicle-removal",
        commands = ["batch:duplicate-vehicle-removal"],
        timeout  = 43200,
        schedule = ["cron(30 21 * * ? *)"],
      },
      {
        name     = "enqueue-ch-compare",
        commands = ["batch:enqueue-ch-compare"],
        timeout  = 1800,
        schedule = ["cron(0 13 ? * 3 *)"],
      },
      {
        name     = "expire-bus-registration",
        commands = ["batch:expire-bus-registration"],
        timeout  = 43200,
        schedule = ["cron(05 13 * * ? *)"],
      },
      {
        name     = "flag-urgent-tasks",
        commands = ["batch:flag-urgent-tasks"],
        timeout  = 1800,
        schedule = ["cron(0 6-20 * * ? *)"],
      },
      {
        name     = "import-users-from-csv",
        commands = ["batch:import-users-from-csv"],
      },
      {
        name     = "inspection-request-email",
        commands = ["batch:inspection-request-email"],
        timeout  = 1800,
        schedule = ["cron(0 13 * * ? *)"],
      },
      {
        name     = "interim-end-date-enforcement",
        commands = ["batch:interim-end-date-enforcement"],
        timeout  = 43200,
        schedule = ["cron(00 13 * * ? *)"],
      },
      {
        name     = "last-tm-letter",
        commands = ["batch:last-tm-letter", "-v"],
        timeout  = 43200,
        schedule = ["cron(30 13 * * ? *)"],
      },
      {
        name     = "licence-status-rules",
        commands = ["batch:licence-status-rules"],
        timeout  = 1800,
        schedule = ["cron(0 6-20 * * ? *)"],
      },
      {
        name     = "process-cl",
        commands = ["batch:process-cl"],
      },
      {
        name     = "process-inbox",
        commands = ["batch:process-inbox"],
        timeout  = 43200,
        schedule = ["cron(45 13 * * ? *)"],
      },
      {
        name     = "process-ntu",
        commands = ["batch:process-ntu"],
        timeout  = 43200,
        schedule = ["cron(0 18 ? * 2-6 *)"],
      },
      {
        name     = "remove-read-audit",
        commands = ["batch:remove-read-audit"],
        timeout  = 43200,
        schedule = ["cron(0 13 ? * 1 *)"],
      },
      {
        name     = "resolve-payments",
        commands = ["batch:resolve-payments"],
        timeout  = 150,
        schedule = ["cron(0/5 6-20 * * ? *)"],
      },
      {
        name     = "system-parameter",
        commands = ["batch:system-parameter"],
      },
      {
        name     = "cancel-unsubmitted-bilateral",
        commands = ["permits:cancel-unsubmitted-bilateral"],
      },
      {
        name     = "close-expired-windows",
        commands = ["permits:close-expired-windows", "-v"],
        timeout  = 43200,
        schedule = ["cron(45 13 * * ? *)"],
      },
      {
        name     = "mark-expired-permits",
        commands = ["permits:mark-expired-permits", "-v"],
        timeout  = 43200,
        schedule = ["cron(15 13 * * ? *)"],
      },
      {
        name     = "permits-reset-test-data",
        commands = ["permits:reset-test-data"],
        type     = "default",
        timeout  = 1800
      },
      {
        name     = "process-queue-general",
        commands = ["queue:process-queue", "--exclude", "que_typ_ch_compare,que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing,que_typ_print,que_typ_disc_printing_print,que_typ_create_com_lic,que_typ_remove_deleted_docs,que_typ_permit_generate,que_typ_permit_print,que_typ_run_ecmt_scoring,que_typ_accept_ecmt_scoring,que_typ_irhp_permits_allocate", "--queue-duration", "110", ],
        timeout  = 120,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-community-licences",
        commands = ["queue:process-queue", "--type", "que_typ_create_com_lic"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-disc-generation",
        commands = ["queue:process-queue", "--type", "que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-disc-print",
        commands = ["queue:process-queue", "--type", "que_typ_disc_printing_print", "--queue-duration", "840"],
        timeout  = 850,
        schedule = ["cron(0/15 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-ecmt-accept",
        commands = ["queue:process-queue", "--type", "que_typ_accept_ecmt_scoring"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-irhp-allocate",
        commands = ["queue:process-queue", "--type", "que_typ_irhp_permits_allocate"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-permit-generation",
        commands = ["queue:process-queue", "--type", "que_typ_permit_generate"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-permit-print",
        commands = ["queue:process-queue", "--type", "que_typ_permit_print", "--queue-duration", "840"],
        timeout  = 850,
        schedule = ["cron(0/15 6-20 * * ? *)"],
      },
      {
        name     = "process-queue-print",
        commands = ["queue:process-queue", "--type", "que_typ_print"],
        timeout  = 90,
        schedule = ["cron(0/2 6-20 * * ? *)"],
      },
      {
        name     = "process-company-profile",
        commands = ["queue:process-company-profile", "-v"],
        timeout  = 150,
        schedule = ["cron(0/5 6-20 * * ? *)"],
      },
      {
        name     = "company-profile-dlq",
        commands = ["queue:company-profile-dlq", "-v"],
        timeout  = 900,
        schedule = ["cron(0/30 6-20 * * ? *)"],
      },
      {
        name     = "process-insolvency",
        commands = ["queue:process-insolvency", "-v"],
        timeout  = 900,
        schedule = ["cron(0/30 6-20 * * ? *)"],
      },
      {
        name     = "process-insolvency-dlq",
        commands = ["queue:process-insolvency-dlq", "-v"],
        timeout  = 900,
        schedule = ["cron(0/30 6-20 * * ? *)"],
      },
      {
        name     = "transxchange-consumer",
        commands = ["queue:transxchange-consumer", "-v"],
        schedule = ["cron(0/2 6-20 * * ? *)"],
        timeout  = 90,
      },
      {
        name  = "liquibase",
        type  = "liquibase",
        queue = "liquibase"
      },
      {
        name     = "sas-mi-extract",
        commands = ["/mnt/data/scripts/sas_mi_extract.sh"],
        type     = "scripts"
      },
      {
        name     = "import-anondb",
        commands = ["/mnt/data/scripts/import_anondb.sh"],
        type     = "scripts"
      },
      {
        name     = "populate-anondb",
        commands = ["/mnt/data/scripts/populate_anondb.sh"],
        type     = "scripts"
      },
      {
        name     = "ni-compliance",
        commands = ["/mnt/data/scripts/ni_dvacompliance.sh"],
        type     = "scripts"
      },
      {
        name     = "first-tm-letter",
        commands = ["batch:first-tm-letter", "-v"],
        timeout  = 43200,
        schedule = ["cron(30 13 * * ? *)"],
      },
      {
        name     = "data-refresh",
        commands = ["/mnt/data/scripts/data_refresh/data_refresh.sh", "qa", "eu-west-1"],
        type     = "scripts"
        cpu      = 2,
        memory   = 8192,
      },
    ]
  }
}

resource "null_resource" "deployed_versions" {
  triggers = {
    deployed_api_image_tag       = var.api_image_tag
    deployed_internal_image_tag  = var.internal_image_tag
    deployed_selfserve_image_tag = var.selfserve_image_tag
    deployed_cli_image_tag       = var.cli_image_tag
    deployed_assets_version      = var.assets_version
  }
}

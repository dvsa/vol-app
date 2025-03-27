locals {
  service_names = ["api", "selfserve", "internal", "cli"]

  legacy_service_names = ["API", "IUWEB", "SSWEB"]

  supporting_service_names = ["search", "liquibase"]

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
    },
    {
      effect = "Allow"
      actions = [
        "s3:PutObject",
      ]
      resources = [
        "arn:aws:s3:::devapp-olcs-pri-olcs-autotest-s3/*",
      ]
    },
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

data "aws_security_group" "this" {
  for_each = toset(local.legacy_service_names)

  name = "DEV/APP/DEV-OLCS-PRI-${each.key}-SG"
}

data "aws_subnets" "this" {
  for_each = toset(setunion(local.legacy_service_names, ["BATCH"]))

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
  for_each = toset(setsubtract(local.service_names, ["cli"]))

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

  legacy_environment = "DEV"

  domain_name    = "dev-dvsacloud.uk"
  assets_version = var.assets_version

  vpc_id = data.aws_vpc.this.id

  elasticache_url = "tcp://cache.dev.olcs.dev-dvsacloud.uk:6379"

  services = {
    "api" = {
      cpu    = 2048
      memory = 4096

      version    = var.api_image_tag
      repository = data.aws_ecr_repository.this["api"].repository_url

      task_iam_role_statements = local.task_iam_role_statements

      subnet_ids = data.aws_subnets.this["API"].ids

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]

      lb_listener_arn           = data.aws_lb_listener.this["API"].arn
      lb_arn                    = data.aws_lb.this["API"].arn
      listener_rule_host_header = "api.*"
    }

    "internal" = {
      cpu    = 2048
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
      lb_arn                    = data.aws_lb.this["IUWEB"].arn
      listener_rule_host_header = "iuweb.*"
    }

    "selfserve" = {
      cpu    = 2048
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
      lb_arn                    = data.aws_lb.this["SSWEB"].arn
      listener_rule_host_header = "ssweb.*"
    }

    "search" = {
      cpu    = 2048
      memory = 4096

      version    = var.search_image_tag
      repository = data.aws_ecr_repository.sservice["search"].repository_url

      listener_rule_enable = false
      add_search_env_info  = true

      task_iam_role_statements = [
        {
          effect = "Allow"
          actions = [
            "secretsmanager:GetSecretValue"
          ]
          resources = [
            data.aws_secretsmanager_secret.this["api"].arn
          ]
        }
      ]

      subnet_ids = data.aws_subnets.this["API"].ids

      security_group_ids = [
        data.aws_security_group.this["API"].id
      ]
    }
  }

  batch = {

    cli_version = var.cli_image_tag

    cli_repository       = data.aws_ecr_repository.this["cli"].repository_url
    search_repository    = data.aws_ecr_repository.sservice["search"].repository_url
    liquibase_repository = data.aws_ecr_repository.sservice["liquibase"].repository_url
    api_secret_file      = data.aws_secretsmanager_secret.this["api"].arn

    task_iam_role_statements = local.task_iam_role_statements

    subnet_ids = data.aws_subnets.this["BATCH"].ids

    alert_emails = [
      "olcs-dev@otc.gov.uk"
    ]

    jobs = [
      {
        name     = "ch-vs-olcs-diffs",
        commands = ["batch:ch-vs-olcs-diffs"],
      },
      {
        name     = "clean-up-variations",
        commands = ["batch:clean-up-variations"],
        timeout  = 43200,
        schedule = "cron(00 13 * * ? *)",
      },
      {
        name     = "cns",
        commands = ["batch:cns"],
        timeout  = 43200,
        schedule = "cron(30 13 ? * 1 *)",
      },
      {
        name     = "create-psv-licence-surrender-task",
        commands = ["batch:create-psv-licence-surrender-task"],
        timeout  = 43200,
        schedule = "cron(00 13 * * ? *)",
      },
      {
        name     = "psv-operator-list-export",
        commands = ["batch:data-gov-uk-export", "-v", "--report-name=psv-operator-list", "--path=/tmp/"],
        timeout  = 43200,
        schedule = "cron(00 13 ? * 1 *)",
      },
      {
        name     = "international-goods-export",
        commands = ["batch:data-gov-uk-export", "-v", "--report-name=international-goods", "--path=/tmp/"],
        timeout  = 43200,
        schedule = "cron(00 13 ? * 1 *)",
      },
      {
        name     = "data-retention-populate",
        commands = ["batch:data-retention", "--populate"],
      },
      {
        name     = "data-retention-precheck",
        commands = ["batch:data-retention", "--precheck"],
      },
      {
        name     = "data-retention-delete",
        commands = ["batch:data-retention", "--delete"],
      },
      {
        name     = "data-retention-postcheck",
        commands = ["batch:data-retention", "--postcheck"],
      },
      {
        name     = "database-maintenance",
        commands = ["batch:database-maintenance"],
      },
      {
        name     = "digital-continuation-reminders",
        commands = ["batch:digital-continuation-reminders"],
        timeout  = 43200,
        schedule = "cron(00 13 * * ? *)",
      },
      {
        name     = "duplicate-vehicle-warning",
        commands = ["batch:duplicate-vehicle-warning"],
        timeout  = 43200,
        schedule = "cron(30 13 ? * 2-6 *)",
      },
      {
        name     = "enqueue-ch-compare",
        commands = ["batch:enqueue-ch-compare"],
        timeout  = 1800,
        schedule = "cron(0 13 ? * 3 *)",
      },
      {
        name     = "expire-bus-registration",
        commands = ["batch:expire-bus-registration"],
        timeout  = 43200,
        schedule = "cron(05 13 * * ? *)",
      },
      {
        name     = "flag-urgent-tasks",
        commands = ["batch:flag-urgent-tasks"],
        timeout  = 1800,
        schedule = "cron(0 8-17 * * ? *)",
      },
      {
        name     = "import-users-from-csv",
        commands = ["batch:import-users-from-csv"],
      },
      {
        name     = "inspection-request-email",
        commands = ["batch:inspection-request-email"],
        timeout  = 1800,
        schedule = "cron(0 13 * * ? *)",
      },
      {
        name     = "interim-end-date-enforcement",
        commands = ["batch:interim-end-date-enforcement"],
        timeout  = 43200,
        schedule = "cron(00 13 * * ? *)",
      },
      {
        name     = "last-tm-letter",
        commands = ["batch:last-tm-letter"],
        timeout  = 43200,
        schedule = "cron(30 13 * * ? *)",
      },
      {
        name     = "licence-status-rules",
        commands = ["batch:licence-status-rules"],
        timeout  = 1800,
        schedule = "cron(0 8-17 * * ? *)",
      },
      {
        name     = "process-cl",
        commands = ["batch:process-cl"],
      },
      {
        name     = "process-inbox",
        commands = ["batch:process-inbox"],
        timeout  = 43200,
        schedule = "cron(45 13 * * ? *)",
      },
      {
        name     = "process-ntu",
        commands = ["batch:process-ntu"],
        timeout  = 43200,
        schedule = "cron(0 18 ? * 2-6 *)",
      },
      {
        name     = "remove-read-audit",
        commands = ["batch:remove-read-audit"],
        timeout  = 43200,
        schedule = "cron(0 13 ? * 1 *)",
      },
      {
        name     = "resolve-payments",
        commands = ["batch:resolve-payments"],
        timeout  = 150,
        schedule = "cron(0/5 8-17 * * ? *)",
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
        commands = ["permits:close-expired-windows"],
        timeout  = 43200,
        schedule = "cron(45 13 * * ? *)",
      },
      {
        name     = "mark-expired-permits",
        commands = ["permits:mark-expired-permits"],
        timeout  = 43200,
        schedule = "cron(15 13 * * ? *)",
      },
      {
        name     = "process-queue-general",
        commands = ["queue:process-queue", "--exclude", "que_typ_ch_compare,que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing,que_typ_print,que_typ_disc_printing_print,que_typ_create_com_lic,que_typ_remove_deleted_docs,que_typ_permit_generate,que_typ_permit_print,que_typ_run_ecmt_scoring,que_typ_accept_ecmt_scoring,que_typ_irhp_permits_allocate"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-community-licences",
        commands = ["queue:process-queue", "--type", "que_typ_create_com_lic"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-disc-generation",
        commands = ["queue:process-queue", "--type", "que_typ_create_gds_vehicle_list,que_typ_create_psv_vehicle_list,que_typ_disc_printing"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-disc-print",
        commands = ["queue:process-queue", "--type", "que_typ_disc_printing_print", "--queue-duration", "840"],
        timeout  = 850,
        schedule = "cron(0/15 8-17 * * ? *)",
      },
      {
        name     = "process-queue-ecmt-accept",
        commands = ["queue:process-queue", "--type", "que_typ_accept_ecmt_scoring"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-irhp-allocate",
        commands = ["queue:process-queue", "--type", "que_typ_run_ecmt_scoring"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-permit-generation",
        commands = ["queue:process-queue", "--type", "que_typ_permit_generate"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-queue-permit-print",
        commands = ["queue:process-queue", "--type", "que_typ_permit_print", "--queue-duration", "840"],
        timeout  = 850,
        schedule = "cron(0/15 8-17 * * ? *)",
      },
      {
        name     = "process-queue-print",
        commands = ["queue:process-queue", "--type", "que_typ_print"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name     = "process-company-profile",
        commands = ["queue:process-company-profile"],
        timeout  = 150,
        schedule = "cron(0/5 8-17 * * ? *)",
      },
      {
        name     = "company-profile-dlq",
        commands = ["queue:company-profile-dlq"],
        timeout  = 900,
        schedule = "cron(0/30 8-17 * * ? *)",
      },
      {
        name     = "process-insolvency",
        commands = ["queue:process-insolvency"],
        timeout  = 900,
        schedule = "cron(0/30 8-17 * * ? *)",
      },
      {
        name     = "process-insolvency-dlq",
        commands = ["queue:process-insolvency-dlq"],
        timeout  = 900,
        schedule = "cron(0/30 8-17 * * ? *)",
      },
      {
        name     = "transxchange-consumer",
        commands = ["queue:transxchange-consumer"],
        timeout  = 90,
        schedule = "cron(0/2 8-17 * * ? *)",
      },
      {
        name  = "liquibase",
        type  = "liquibase",
        queue = "liquibase"
      },
      {
        name = "search",
        type = "search"
      },
      {
        name     = "sas-mi-extract",
        commands = ["source /mnt/data/sas_mi_extract.sh"],
        type     = "scripts"
      },
      {
        name     = "import-anondb",
        commands = ["source /mnt/data/import_anondb.sh"],
        type     = "scripts"
      },
      {
        name     = "populate-anondb",
        commands = ["source /mnt/data/populate_anondb.sh"],
        type     = "scripts"
      },
      {
        name     = "ni-compliance",
        commands = ["source /mnt/data/ni_dvacomplaince.sh"],
        type     = "scripts"
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
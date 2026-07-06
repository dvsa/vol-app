variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

variable "legacy_environment" {
  type        = string
  description = "The legacy environment to deploy use"
}

variable "domain_env" {
  type        = string
  description = "The domain environment to deploy use"
}

variable "domain_name" {
  type        = string
  description = "The domain name for the environment"
}

variable "assets_version" {
  type        = string
  description = "The version of the assets"
}

variable "vpc_id" {
  type        = string
  description = "The VPC ID"
}

variable "elasticache_url" {
  type        = string
  description = "The URL of the Elasticache cluster"
}
variable "services" {
  type = map(object({
    version    = string
    repository = string
    cpu        = number
    memory     = number
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
    task_exec_iam_role_statements = optional(list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    })), [])
    add_cdn_url_to_env          = optional(bool, false)
    set_custom_port             = optional(bool, false)
    enable_autoscaling_policies = optional(bool, true)
    lb_arn                      = optional(string)
    lb_listener_arn             = optional(string)
    iuweb_pub_listener_arn      = optional(string)
    // The reason for this was to enable the parallel running of ECS and EC2 services.
    // This boolean will control the flow of traffic. If `true`, traffic will go to ECS. If `false`, traffic will go to EC2.
    // Can be removed when EC2 services are removed.
    listener_rule_enable              = optional(bool, true)
    listener_rule_priority            = optional(number, 10)
    listener_rule_host_header         = optional(list(string), ["*"])
    listener_rule_host_header_proving = optional(list(string), ["*"])
    security_group_ids                = list(string)
    subnet_ids                        = list(string)
    vpc_id                            = optional(string, null)
  }))
  description = "The services to deploy"
  default     = {}
}

variable "batch" {
  description = "Configuration for the batch process"
  type = object({
    cli_version          = string
    cli_repository       = string
    liquibase_repository = string
    api_secret_file      = string
    subnet_ids           = list(string)
    alert_emails         = optional(list(string))
    task_iam_role_statements = list(object({
      effect    = string
      actions   = list(string)
      resources = list(string)
    }))
    jobs = list(object({
      name     = string
      type     = optional(string, "default")
      queue    = optional(string, "default")
      commands = optional(list(string))
      cpu      = optional(number, 1)
      memory   = optional(number, 4096)
      timeout  = optional(number, 300)
      schedule = optional(list(string), [])
    }))
  })
}

variable "application_parameters" {
  type = map(object({
    domain                                       = string
    shd_proxy                                    = string
    olcs_webdav                                  = string
    olcs_document_store_backend                  = string
    olcs_document_store_s3_bucket                = string
    olcs_document_store_s3_key_prefix            = string
    olcs_aws_sqs_ch_get_queue                    = string
    olcs_aws_sqs_ch_get_dlq                      = string
    olcs_aws_sqs_ch_insolvency_queue             = string
    olcs_aws_sqs_ch_insolvency_dlq               = string
    olcs_cpmsserver                              = string
    olcs_send_all_mail_to                        = string
    olcs_from_email                              = string
    olcs_ss_uri                                  = string
    olcs_iu_uri                                  = string
    olcs_aws_s3_role_arn                         = string
    olcs_notify_template_en_gb                   = string
    olcs_notify_template_cy_gb                   = string
    olcs_notify_test_dsn                         = string
    olcs_mail_dsn                                = string
    transxchange_aws_consumer_role               = string
    transxchange_aws_sqs_output_uri              = string
    transxchange_aws_s3_output_bucket            = string
    redis_cache_fqdn                             = string
    olcs_dvla_search_base_uri                    = string
    aws_cognito_region                           = string
    lar_base_uri                                 = string
    govuk_account_discovery_endpoint             = string
    govuk_account_client_id                      = string
    govuk_account_public_key                     = string
    govuk_account_id_assurance_public_key        = string
    govuk_account_id_assurance_issuer            = string
    govuk_account_core_identity_did_document_url = string
    operator_reports_api_url                     = string
    address_service_url                          = string
    address_service_azure_token_scope            = string
    address_service_azure_client_id              = string
    address_service_azure_token_url              = string
    olcs_txc_client_id                           = string
    olcs_txc_scope                               = string
    olcs_txc_token_url                           = string
    transxchange_uri                             = string
    transxchange_aws_s3_input_bucket             = string
    data-gov-uk-export-s3uri                     = string
    data-dva-ni-export-s3uri                     = string
    olcs_natreg_client_id                        = string
    olcs_natreg_token_url                        = string
    olcs_natreg_client_scope                     = string
    pdf_service_uri                              = string
    cups_server_url                              = string
    env                                          = string
    olcs_iu_cookie                               = string
    olcs_ss_cookie                               = string
    olcs_google_gtm_auth                         = string
    olcs_google_gtm_preview                      = string
    verify_forwarder_valid_origin                = string
    assets_url                                   = string
    assets_cache_busting_strategy                = string
    ecs_api_hostname                             = string
    log_level                                    = string
  }))
}
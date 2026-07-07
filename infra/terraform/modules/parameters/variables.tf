variable "environment" {
  type        = string
  description = "The environment to deploy to"
}

/*
    * Log Level
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

variable "application_parameters" {
  type = object({
    address_service_azure_client_id              = string
    address_service_azure_token_scope            = string
    address_service_azure_token_url              = string
    address_service_url                          = string
    assets_cache_busting_strategy                = string
    assets_url                                   = string
    aws_cognito_region                           = string
    cups_server_url                              = string
    data-dva-ni-export-s3uri                     = string
    data-gov-uk-export-s3uri                     = string
    domain                                       = string
    ecs_api_hostname                             = string
    env                                          = string
    govuk_account_client_id                      = string
    govuk_account_core_identity_did_document_url = string
    govuk_account_discovery_endpoint             = string
    govuk_account_id_assurance_issuer            = string
    govuk_account_id_assurance_public_key        = string
    govuk_account_public_key                     = string
    govuk_account_private_key_algorithm          = string
    lar_base_uri                                 = string
    log_level                                    = string
    olcs_aws_s3_role_arn                         = string
    olcs_aws_sqs_ch_get_dlq                      = string
    olcs_aws_sqs_ch_get_queue                    = string
    olcs_aws_sqs_ch_insolvency_dlq               = string
    olcs_aws_sqs_ch_insolvency_queue             = string
    olcs_cpmsserver                              = string
    olcs_document_store_backend                  = string
    olcs_document_store_s3_bucket                = string
    olcs_document_store_s3_key_prefix            = string
    olcs_dvla_search_base_uri                    = string
    olcs_from_email                              = string
    olcs_google_gtm_auth                         = string
    olcs_google_gtm_preview                      = string
    olcs_imap_port                               = string
    olcs_imap_ssl                                = string
    olcs_imap_user                               = string
    olcs_iu_cookie                               = string
    olcs_iu_uri                                  = string
    olcs_mail_dsn                                = string
    olcs_natreg_client_id                        = string
    olcs_natreg_client_scope                     = string
    olcs_natreg_token_url                        = string
    olcs_notify_template_cy_gb                   = string
    olcs_notify_template_en_gb                   = string
    olcs_notify_test_dsn                         = string
    olcs_send_all_mail_to                        = string
    olcs_ss_cookie                               = string
    olcs_ss_uri                                  = string
    olcs_txc_client_id                           = string
    olcs_txc_scope                               = string
    olcs_txc_token_url                           = string
    olcs_webdav                                  = string
    operator_reports_api_url                     = string
    pdf_service_uri                              = string
    redis_cache_fqdn                             = string
    shd_proxy                                    = string
    transxchange_aws_consumer_role               = string
    transxchange_aws_s3_input_bucket             = string
    transxchange_aws_s3_output_bucket            = string
    transxchange_aws_sqs_output_uri              = string
    transxchange_uri                             = string
    verify_forwarder_valid_origin                = string
  })
}
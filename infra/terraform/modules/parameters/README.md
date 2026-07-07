<!-- BEGIN_TF_DOCS -->
## Requirements

| Name | Version |
|------|---------|
| <a name="requirement_terraform"></a> [terraform](#requirement\_terraform) | >= 1.0 |
| <a name="requirement_aws"></a> [aws](#requirement\_aws) | >= 5.0.0 |

## Providers

No providers.

## Modules

| Name | Source | Version |
|------|--------|---------|
| <a name="module_application_paramters"></a> [application\_paramters](#module\_application\_paramters) | terraform-aws-modules/ssm-parameter/aws | n/a |

## Resources

No resources.

## Inputs

| Name | Description | Type | Default | Required |
|------|-------------|------|---------|:--------:|
| <a name="input_application_parameters"></a> [application\_parameters](#input\_application\_parameters) | n/a | <pre>object({<br/>    address_service_azure_client_id              = string<br/>    address_service_azure_token_scope            = string<br/>    address_service_azure_token_url              = string<br/>    address_service_url                          = string<br/>    assets_cache_busting_strategy                = string<br/>    assets_url                                   = string<br/>    aws_cognito_region                           = string<br/>    cups_server_url                              = string<br/>    data-dva-ni-export-s3uri                     = string<br/>    data-gov-uk-export-s3uri                     = string<br/>    domain                                       = string<br/>    ecs_api_hostname                             = string<br/>    env                                          = string<br/>    govuk_account_client_id                      = string<br/>    govuk_account_core_identity_did_document_url = string<br/>    govuk_account_discovery_endpoint             = string<br/>    govuk_account_id_assurance_issuer            = string<br/>    govuk_account_id_assurance_public_key        = string<br/>    govuk_account_public_key                     = string<br/>    lar_base_uri                                 = string<br/>    log_level                                    = string<br/>    olcs_aws_s3_role_arn                         = string<br/>    olcs_aws_sqs_ch_get_dlq                      = string<br/>    olcs_aws_sqs_ch_get_queue                    = string<br/>    olcs_aws_sqs_ch_insolvency_dlq               = string<br/>    olcs_aws_sqs_ch_insolvency_queue             = string<br/>    olcs_cpmsserver                              = string<br/>    olcs_document_store_backend                  = string<br/>    olcs_document_store_s3_bucket                = string<br/>    olcs_document_store_s3_key_prefix            = string<br/>    olcs_dvla_search_base_uri                    = string<br/>    olcs_from_email                              = string<br/>    olcs_google_gtm_auth                         = string<br/>    olcs_google_gtm_preview                      = string<br/>    olcs_imap_port                               = string<br/>    olcs_imap_ssl                                = string<br/>    olcs_imap_user                               = string<br/>    olcs_iu_cookie                               = string<br/>    olcs_iu_uri                                  = string<br/>    olcs_mail_dsn                                = string<br/>    olcs_natreg_client_id                        = string<br/>    olcs_natreg_client_scope                     = string<br/>    olcs_natreg_token_url                        = string<br/>    olcs_notify_template_cy_gb                   = string<br/>    olcs_notify_template_en_gb                   = string<br/>    olcs_notify_test_dsn                         = string<br/>    olcs_send_all_mail_to                        = string<br/>    olcs_ss_cookie                               = string<br/>    olcs_ss_uri                                  = string<br/>    olcs_txc_client_id                           = string<br/>    olcs_txc_scope                               = string<br/>    olcs_txc_token_url                           = string<br/>    olcs_webdav                                  = string<br/>    operator_reports_api_url                     = string<br/>    pdf_service_uri                              = string<br/>    redis_cache_fqdn                             = string<br/>    shd_proxy                                    = string<br/>    transxchange_aws_consumer_role               = string<br/>    transxchange_aws_s3_input_bucket             = string<br/>    transxchange_aws_s3_output_bucket            = string<br/>    transxchange_aws_sqs_output_uri              = string<br/>    transxchange_uri                             = string<br/>    verify_forwarder_valid_origin                = string<br/>  })</pre> | n/a | yes |
| <a name="input_environment"></a> [environment](#input\_environment) | The environment to deploy to | `string` | n/a | yes |

## Outputs

No outputs.
<!-- END_TF_DOCS -->
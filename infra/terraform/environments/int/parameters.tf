module "parameters" {
  source = "../../modules/parameters"

  environment = "int"

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
}
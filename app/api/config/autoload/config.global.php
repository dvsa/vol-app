<?php

$environment = getenv('ENVIRONMENT_NAME');

// All this logic to do with environments should be part of parameter store instead.
// But for now, it's not. So we have to do it here.
$isProduction = strtoupper($environment) === 'APP';

$isProductionAccount = in_array(strtoupper($environment), ['INT', 'PP', 'APP']);

$doctrine_connection_params = [
    // *Environment specific*
    'host' => 'olcsdb-rds.%domain%',
    // *Environment specific*
    'port' => '3306',
    // *Environment specific*
    'user' => 'olcsapi',
    // *Environment specific*
    'password' => '%olcs_api_rds_password%',
    // *Environment specific*
    'dbname' => 'OLCS_RDS_OLCSDB',
];

return [
    // Postcode/Address service
    'address' => [
        'client' => [
            // URI e.g. http://postcode.cit.olcs.mgt.mtpdvsa/ *Environment specific*
            'baseuri' => 'http://address.%domain%/'
        ]
    ],

    // Elastic search
    'elastic_search' => [
        // Hostname e.g. elasticsearch-dev.olcs.mgt.mtpdvsa *Environment specific*
        'host' => 'searchv6.%domain%',
        // Port, e.g. 9200
        'port' => '443',
        // Transport protocol
        'transport' => 'Https',
        // Additional CURL options
        'curl' => [
            CURLOPT_SSL_VERIFYHOST => false,
        ],
    ],

    // Doctrine
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                // Database connection details
                'params' => $doctrine_connection_params,
            ],
            'export' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                // Database connection details
                'params' => $doctrine_connection_params,
            ],
        ],
        'driver' => [
            'EntityDriver' => [
                'cache' => 'apcu'
            ],
            'translatable_metadata_driver' => [
                'cache' => 'apcu',
            ]
        ],
        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'apcu',
                'generate_proxies' => true,
                // Log SQL queries to the OLCS application log file
                //'sql_logger' => 'DoctrineLogger',
            ]
        ],
        'migrations' => [
            'em' => 'default',
            'orm_default' => [
                'directory' => 'data/Migrations',
                'namespace' => 'Migrations',
                'table'     => 'migrations',
                'column'    => 'version',
                'all_or_nothing' => true,
                'check_database_platform' => true,
            ],
        ],
    ],

    // Companies house XML gateway credentials
    'companies_house_credentials' => [
        // Companies house XML gateway userID *Environment specific*
        'userId' => '%olcs_companieshousexmluserid%',
        // Companies house XML gateway password *Environment specific*
        'password' => '%olcs_companieshousexmlpassword%',
    ],

    // Set the following if you need to go via a proxy to get to Companies house XML gateway
    // *Environment specific*
    'companies_house_connection' => [
        'proxy' => "%shd_proxy%"
    ],

    // Document service
    'document_share' => [
        'client' => [
            // Document service workspace "olcs"
            'workspace' => 'olcs',
            'webdav_baseuri' => '%olcs_webdav%',
            'username' => 'olcs_app',
            'password' => '%olcs_api_opendj_password%'
        ],
        'invalid_defined_mime_types' => [
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'potm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        ],
        'valid_mime_types' => [
            'application/json',
            'application/msword',
            'application/pdf',
            'application/rtf',
            'application/vnd.ms-excel',
            'application/vnd.ms-excel',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-powerpoint',
            'application/vnd.ms-xpsdocument',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.graphics',
            'application/vnd.oasis.opendocument.image',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.presentation',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.spreadsheet',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.text',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/x-7z-compressed',
            'application/x-msmetafile',
            'application/xml',
            'application/xml',
            'application/zip',
            'audio/flac',
            'audio/m4a',
            'audio/mpeg',
            'audio/mpeg',
            'audio/ogg',
            'audio/x-aac',
            'audio/x-aiff',
            'audio/x-aiff',
            'audio/x-ms-wma',
            'audio/x-wav',
            'image/bmp',
            'image/gif',
            'image/jpeg',
            'image/jpeg',
            'image/png',
            'image/tiff',
            'image/tiff',
            'image/vnd.djvu',
            'image/vnd.dwg',
            'image/vnd.dxf',
            'image/webp',
            'message/rfc822',
            'text/csv',
            'text/html',
            'text/plain',
            'video/3gpp',
            'video/webm',
        ]
    ],

    // Asset path, URI to olcs-static (CSS, JS, etc) *Environment specific*
    'asset_path' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? '%assets_url%' : '/static/public'),

    // Companies house RESTful API
    'companies_house' => [
        'http' => [
            // Set the following if you need to go via a proxy to get to Companies House RESTful API
            // *Environment specific*
            'curloptions' => [
                CURLOPT_PROXY => "http://%shd_proxy%",
                // Companies House API key followed by a colon
                CURLOPT_USERPWD => "%olcs_companieshouseapikey%:",
            ],
        ],
        'auth' => [
            // Companies House API key (register one at https://developer.companieshouse.gov.uk/] *Environment specific*
            'username' => "%olcs_companieshouseapikey%",
            // Leave this empty
            'password' => '',
        ],
        'client' => [
            'baseuri' => "%companies_house_api_base_uri%",
        ],
    ],

    // SQS Queues
    'message_queue' => [
        'CompanyProfile_URL' => "%olcs_aws_sqs_base_uri%/%olcs_aws_account_number%/%olcs_aws_sqs_ch_get_queue%",
        'CompanyProfileDlq_URL' => "%olcs_aws_sqs_base_uri%/%olcs_aws_account_number%/%olcs_aws_sqs_ch_get_dlq%",
        'ProcessInsolvency_URL' => "%olcs_aws_sqs_base_uri%/%olcs_aws_account_number%/%olcs_aws_sqs_ch_insolvency_queue%",
        'ProcessInsolvencyDlq_URL' => "%olcs_aws_sqs_base_uri%/%olcs_aws_account_number%/%olcs_aws_sqs_ch_insolvency_dlq%",
        'TransXChangeConsumer_URL' => "%transxchange_aws_sqs_output_uri%",
    ],

    'company_house_dlq' => [
        'notification_email_address' => "%company_house_dlq_notification_email_address%"
    ],

    // CPMS service
    'cpms_api' => [
        'logger_alias' => 'Logger', // Laminas logger service manager alias - use 'Logger' for the main OLCS log
        'identity_provider' => 'CpmsIdentityProvider', // Should implement CpmsClient\Authenticate\IdentityProviderInterface
        'enable_cache' => true,
        'cache_storage' => 'array',
        'rest_client' => [
            'options' => [
                //CPMS API version to use
                'version' => 2,
                // CPMS hostname e.g. 'payment-service.psqa-ap01.ps.npm' *Environment specific*
                'domain' => "%olcs_cpmsserver%",
                'grant_type' => 'client_credentials',
                'timeout' => 15.0,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
        ],
    ],

    // CPMS service authentication - used by CpmsIdentityProvider service
    // CPMS service authentication - used by CpmsIdentityProvider service
    'cpms_credentials' => [
        // CPMS user ID *Environment specific*
        // (this can be left as is, will be deprecated in the future once authentication is implemented)
        'user_id' => '1234',
        // CPMS client ID *Environment specific*
        'client_id' => "%olcs_cpmsclientid%",
        // CPMS Client secret *Environment specific*
        'client_secret' => "%olcs_cpmssecret%",
        // CPMS client ID for NI *Environment specific*
        'client_id_ni' => "%olcs_cpmsclientid_ni%",
        // CPMS Client secret for NI *Environment specific*
        'client_secret_ni' => "%olcs_cpmssecret_ni%",
    ],

    // Email config
    'email' => [
        // Debugging option forces all email to be sent to an address
        // Selfserve/external URI e.g. http://demo_dvsa-selfserve.web03.olcs.mgt.mtpdvsa *Environment specific*
        'send_all_mail_to' => ($isProductionAccount && !$isProduction) ? '%olcs_send_all_mail_to%' : null,
        'from_name' => 'OLCS do not reply',
        'from_email' => '%olcs_from_email%',
        'selfserve_uri' => '%olcs_ss_uri%',
        'internal_uri' => '%olcs_iu_uri%',
    ],
    'awsOptions' => array_filter([
        'region' => '%olcs_aws_region%',
        'version' => '%olcs_aws_version%',
        's3' => [
            'use_path_style_endpoint' => false,
        ],
        's3Options' => $isProductionAccount ? null : [
            'roleArn' => '%olcs_aws_s3_role_arn%',
            'roleSessionName' => '%olcs_aws_s3_role_session_name%'
        ],
        'sts' => [
            'sts_regional_endpoints' => 'regional'
        ]
    ]),
    'mail' => ($isProductionAccount && \Aws\Credentials\CredentialProvider::shouldUseEcs())
        ? [
            'type' => '\Laminas\Mail\Transport\Smtp',
            'options' => [
                'name' => '%olcs_email_host%',
                'host' => '%olcs_email_host%',
                'port' => '%olcs_email_port%',
                'connection_config' => [
                    'username' => 'null',
                    'password' => 'null',
                    'port' => '%olcs_email_port%',
                ],
            ],
        ]
        : ($isProductionAccount ? [] : [
            'type' => \Dvsa\Olcs\Email\Transport\MultiTransport::class,
            'options' => [
                'transport' => [
                    ['type' => 'SMTP', 'options' => ['name' => '%olcs_email_host%', 'host' => '%olcs_email_host%', 'port' => '%olcs_email_port%']],
                    ['type' => \Dvsa\Olcs\Email\Transport\S3File::class, 'options' => ['bucket' => 'devapp-olcs-pri-olcs-autotest-s3', 'key' => '%domain%/email']],
                ]
            ],
        ]),

    'mailboxes' => [
        // IMAP connection to a the mailbox for reading inspection request emails
        'inspection_request' => [
            // IMAP hostname *Environment specific*
            'host' => '%olcs_imap_host%',
            // IMAP user *Environment specific*
            'user' => '%olcs_imap_user%',
            // IMAP password *Environment specific*
            'password' => '%olcs_imap_password%',
            // IMAP port 993 *Environment specific*
            'port' => '%olcs_imap_port%',
            // SSL (0 or 1)
            'ssl' => '%olcs_imap_ssl%',
        ],
    ],

    'ebsr' => [
        'transexchange_publisher' => [
            'new_uri' => '%transxchange_uri%',
            'options' => [
                'adapter' => \Laminas\Http\Client\Adapter\Proxy::class,
                'proxy_host' => 'proxy.%domain%',
                'proxy_port' => 3128,
                'timeout' => 30
            ],
            'consumer_proxy' => 'http://%shd_proxy%',
            'oauth2' => [
                'client_id' => '%olcs_txc_client_id%',
                'client_secret' => '%olcs_txc_client_secret%',
                'token_url' => '%olcs_txc_token_url%',
                'scope' => '%olcs_txc_scope%',
                'proxy' => 'http://%shd_proxy%',
                'service_name' => 'TransXchange',
            ],
        ],
        'tmp_extra_path' => '/EBSR', //extra path to ebsr files within /tmp
        //debug only - validation must always be set to true in production
        'validate' => [
            'xml_structure' => true,
            'bus_registration' => true,
            'processed_data' => true,
            'short_notice' => true
        ],
        // the input bucket for TransXChange, where the xml is placed
        'input_s3_bucket' => '%transxchange_aws_s3_input_bucket%',
        // The output bucket for TransXChange. This bucket will container the resulting PDFs.
        'output_s3_bucket' => '%transxchange_aws_s3_output_bucket%',
        // The cross account role that VOL will assume to access the output bucket and output SQS queue.
        'txc_consumer_role_arn' => '%transxchange_aws_consumer_role%',
        // The maximum number of SQS message to consume per run.
        'max_queue_messages_per_run' => '100',
    ],
    'nr' => [
        // @to-do currently waiting on the actual nr address
        'inr_service' => [
            'uri' => '%olcs_natreg_uri%',
            'adapter' => Laminas\Http\Client\Adapter\Curl::class,
            'oauth2' => [ // if client['headers']['Authorization'] is not set, then this will be used to get token
                'client_id' => '%olcs_natreg_client_id%', //param
                'client_secret' => '%olcs_natreg_client_secret%', // secret
                'token_url' => '%olcs_natreg_token_url%', //param
                'scope' => '%olcs_natreg_client_scope%', //param
                'proxy' => 'http://%shd_proxy%',
            ]
        ],
        'repute_url' => [
            'uri' => '%olcs_natreg_repute%'
        ],
    ],

    // CUPS print server
    'print' => [
        'server' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? '%cups_server_url%' : 'print.%domain%:631'),
    ],

    // If this value is populated then printing will use this service,
    // if it is not populated or missing then the Libreoffice converter will be used
    'convert_to_pdf' => [
        'uri' => \Aws\Credentials\CredentialProvider::shouldUseEcs()
            ? '%pdf_service_uri%'
            : 'http://renderer.%domain%:8080/convert-document',
    ],

    /**
     * Configure the location of the application log
     */
    'log' => [
        'Logger' => [
            'writers' => [
                'full' => [
                    'options' => [
                        'stream' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? 'php://stdout' : '/var/log/dvsa/olcs-api/api.log'),
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'priority' => '%log_level%'
                                ]
                            ],
                        ]
                    ],
                ]
            ]
        ],
        'ExceptionLogger' => [
            'writers' => [
                'full' => [
                    'options' => [
                        'stream' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? 'php://stderr' : '/var/log/dvsa/olcs-api/api.log'),
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'priority' => '%log_level%'
                                ]
                            ],
                        ]
                    ],
                ]
            ]
        ]
    ],

    // Path to VI extract data
    'vi_extract_files' => [
        'export_path' => '/tmp/ViExtract'
    ],

    // s3 bucket URI to export CSV data for data.gov.uk
    'data-gov-uk-export' => [
        's3_uri' => '%data-gov-uk-export-s3uri%'
    ],

    'data-dva-ni-export' => [
        's3_uri' => '%data-dva-ni-export-s3uri%'
    ],

    // Path to export CSV data for Companies House differences
    'ch-vs-olcs-export' => [
        'path' => '/tmp/companyHouse_vs_Olcs',
    ],

    // Nysiis configuration
    'nysiis' => [
        'rest' => [
            'uri' => 'http://localhost:8080/nysiis-/nysiis/convert',
            'options' => [
                'timeout' => 5
            ]
        ]
    ],

    'allow_file_upload' => [
        // list of allowed file extensions that can be uploaded
        'extensions' => [
            // for external users
            'external'
            => 'doc,docb,docm,docx,ppt,pptx,pptm,pps,ppsm,ppsx,sldx,sldm,xls,xlsb,xlsx,xlsm,xlw'
                . ',odt,ods,odp,odt,odm,odg,odp,ods,odi,odg'
                . ',txt,csv,rtf,xml,pdf,log,xml,json,djvu,xps,oxps'
                . ',jpeg,jpg,png,tif,tiff,gif,jfif,bmp,webp,emf,dwg,dxf,wmf'
                . ',zip,7z',
            // for internal users
            'internal'
            => 'doc,docb,docm,docx,ppt,pptx,pptm,pps,ppsm,ppsx,sldx,sldm,xls,xlsb,xlsx,xlsm,xlw'
                . ',odt,ods,odp,odt,odm,odg,odp,ods,odi,odg'
                . ',txt,csv,rtf,xml,pdf,log,xml,json,djvu,xps,oxps'
                . ',jpeg,jpg,png,tif,tiff,gif,jfif,bmp,webp,emf,dwg,dxf,wmf'
                . ',zip,7z'
                . ',scan,eml'
                . ',mp2,mp3,m4a,3gp,wav,aif,aiff,flac,ogg,wma,ape,aac,amr,webm,ac3',
        ]
    ],

    //If we find these strings in xml validator error messages, don't return the message to the user.
    //This is so we can avoid showing things like directory paths to the user in cases such as schema import errors
    'xml_valid_message_exclude' => [
        '/opt/dvsa',
        'Skipping import of schema'
    ],

    // Specifies the batch size to use for disc printing
    'disc_printing' => [
        // Number of discs to print for each queue job
        'disc_batch_size' => 120,
        // Number of PSV vehicle lists to print for each queue job
        'psv_vehicle_list_batch_size' => 120,
        // Number of GOODS vehicle lists to print for each queue job
        'gv_vehicle_list_batch_size' => 120,
    ],

    // Key used to encrypt data stored in the Doctrine EncryptedStringType
    'olcs-doctrine' => [
        'encryption_key' => '%olcs_doctrine_encryption_key%'
    ],

    'cache-encryption' => [
        'node_suffix' => 'api',
        'adapter' => '%cache_encryption_adapter%',
        'options' => [
            'algo' => '%cache_encryption_algo%',
            'mode' => '%cache_encryption_mode%',
        ],
        'secrets' => [
            'node' => '%cache_encryption_secret_api%',
            'shared' => '%cache_encryption_secret_shared%',
        ],
    ],

    'caches' => [
        'default-cache' => [
            'adapter' => Laminas\Cache\Storage\Adapter\Redis::class,
            'options' => [
                'server' => [
                    'host' => '%redis_cache_fqdn%',
                    'port' => 6379,
                ],
                'lib_options' => [
                    \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_IGBINARY
                ],
                'ttl' => 3600, //one hour, likely to be overridden based on use case
                'namespace' => 'zfcache',
            ],
            'plugins' => [
                [
                    'name' => 'exception_handler',
                    'options' => [
                        'throw_exceptions' => false,
                    ],
                ],
            ],
        ],
    ],

    'dvla_search' => [
        'base_uri' => '%olcs_dvla_search_base_uri%',
        'api_key' => '%olcs_dvla_search_api_key%',
        'proxy' => "http://%shd_proxy%",
    ],

    'auth' => [
        'default_adapter' => 'cognito',
        'identity_provider' => \Dvsa\Olcs\Api\Rbac\JWTIdentityProvider::class,
        'adapters' => [
            'cognito' => [
                'adapter' => \Dvsa\Olcs\Auth\Adapter\CognitoAdapter::class,
                'clientId' => '%aws_cognito_client_id%',
                'clientSecret' => '%aws_cognito_client_secret%',
                'poolId' => '%aws_cognito_pool_id%',
                'region' => '%aws_cognito_region%',
                'nbfLeeway' => 120,
                'http' => [
                    'proxy' => [
                        'http' => 'http://%shd_proxy%',
                        'https' => 'http://%shd_proxy%',
                    ],
                ],
            ],
        ],
    ],
    'acquired_rights' => [
        // enables the expiry check, lookup of reference number, status check and dob comparison
        'check_enabled' => true,
        // determines when a user is no longer able to use an acquired rights reference number
        'expiry' => DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC7231, 'Tue, 20 May 2025 22:59:59 GMT'), // Tue, 20 May 2025 23:59:59 BST
        // guzzle client options
        'client' => [ // Client configuration passed to Guzzle client. base_url is required and must be set to API root.
            'base_uri' => '%lar_base_uri%',
            'timeout' => 30,
            'headers' => [
                'x-api-key' => '%lar_vol_ref_lookup_api_key%'
            ],
        ],
    ],
    'govuk_account' => [
        'discovery_endpoint' => '%govuk_account_discovery_endpoint%',
        'client_id' => '%govuk_account_client_id%',
        'keys' => [
            'algorithm' => '%govuk_account_private_key_algorithm%',
            'private_key' => '%govuk_account_private_key%',
            'public_key' => '%govuk_account_public_key%',
        ],
        'redirect_uri' => [
            'logged_in' => '%olcs_ss_uri%/govuk-id/loggedin',
            'logged_out' => '%olcs_ss_uri%/govuk-id/loggedout',
        ],
        'core_identity_did_document_url' => '%govuk_account_core_identity_did_document_url%',
        'expected_core_identity_issuer' => '%govuk_account_id_assurance_issuer%',
        'proxy' => 'http://%shd_proxy%',
    ],
    'top-report-link' => [
        'targetUrl' => '%operator_reports_api_url%',
        'apiKey' => '%dvsa_reports_api_key%',
        'proxy' => 'http://%shd_proxy%',
    ],
    'dvsa_address_service' => [
        'client' => [ // Guzzle client options
            'base_uri' => '%address_service_url%',
            'headers' => [],
            'proxy' => 'http://%shd_proxy%',
        ],
        'oauth2' => [
            'client_id' => '%address_service_azure_client_id%',
            'client_secret' => '%address_service_azure_client_secret%',
            'token_url' => '%address_service_azure_token_url%',
            'scope' => '%address_service_azure_token_scope%',
            'proxy' => 'http://%shd_proxy%',
        ],
    ],
];

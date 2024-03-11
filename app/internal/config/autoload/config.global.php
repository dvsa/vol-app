<?php

$environment = getenv('ENVIRONMENT_NAME');

// All this logic to do with environments should be part of parameter store instead.
// But for now, it's not. So we have to do it here.
$isProduction = strtoupper($environment) === 'APP';

return [
    'version' => $isProduction ? null : [
        'environment' => $environment,
        'release' => (file_exists(__DIR__ . '/../version') ? file_get_contents(__DIR__ . '/../version') : ''),
        'description' => '%domain%',
    ],
    'api_router' => [
        'routes' => [
            'api' => [
                'child_routes' => [
                    'backend' => [
                        'options' => [
                            // Backend service URI *Environment specific*
                            'route' => 'api.%domain%'
                        ]
                    ]
                ]
            ]
        ]
    ],

    // Service addresses
    'service_api_mapping' => [
        'endpoints' => [
            // Backend service URI *Environment specific*
            'backend' => [
                'url' => 'http://api.%domain%/',
                'options' => [
                    'adapter' => \Laminas\Http\Client\Adapter\Curl::class,
                    'timeout' => 60,
                ]
            ],
            // Postcode/Address service URI *Environment specific*
            'postcode' => [
                'url' => 'http://address.%domain%/',
                'options' => [
                    'adapter' => \Laminas\Http\Client\Adapter\Curl::class,
                    'timeout' => 60,
                ]
            ],
        ]
    ],

    // Document service
    'windows_7_document_share' => [
        // File hyperlink to document
        'uri_pattern' => 'file://///file.%domain%/olcs/%%s'
    ],
    'windows_10_document_share' => [
        // File hyperlink to document
        'uri_pattern' => 'ms-word:ofe|u|http://webdav.%domain%/documents/olcs/%%s'
    ],
    'northern_i_document_share' => [
        'uri_pattern' => 'ms-word:ofe|u|http://webdav.%domain%/documents/olcs/%%s'
    ],

    // WebDav
    'webdav' => [
        // Private key used to sign JWT tokens when generating WebDAV links. (PEM Format)
        // Can be a path to a .pem file or base64 encoded private key in PEM format.
        'private_key' => '/etc/pki/tls/private/jwtRS256.pem',
        // The default length in seconds the JWT is valid for.
        'default_lifetime_seconds' => 21600,
        // The URL pattern for a WebDAV URL. JWT followed by Document Path will be sprintf'ed into this value.
        'url_pattern' => 'ms-word:ofe|u|https://iuweb.%domain%/documents-dav/%%s/olcs/%%s'
    ],

    // Asset path, URI to olcs-static (CSS, JS, etc] *Environment specific*
    'asset_path' => '/static/public',

    'openam' => [
        'url' => 'http://iuauth.%domain%:8080/secure/',
        'realm' => 'internal',
        'cookie' => [
            'domain' => '%olcs_iu_cookie%',
        ]
    ],

    /**
     * Configure the location of the application log
     */
    'log' => [
        'Logger' => [
            'writers' => [
                'full' => [
                    'options' => [
                        'stream' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? 'php://stdout' : '/var/log/dvsa/olcs-iuweb/iuweb.log')
                    ],
                ]
            ]
        ],
        'ExceptionLogger' => [
            'writers' => [
                'full' => [
                    'options' => [
                        'stream' => (\Aws\Credentials\CredentialProvider::shouldUseEcs() ? 'php://stderr' : '/var/log/dvsa/olcs-iuweb/iuweb.log')
                    ],
                ]
            ]
        ]
    ],

    // enable the virus scanning of uploaded files
    // To disable scanning comment out this section or set 'cliCommand' to ""
    'antiVirus' => [
        'cliCommand' => 'clamdscan --no-summary --remove %%s',
    ],

    // Shows a preview for these file extensions
    'allow_file_preview' => [
        'extensions' => [
            'images' => 'jpeg,jpg,png,tif,tiff,gif,jfif,bmp'
        ]
    ],

    'cache-encryption' => [
        'node_suffix' => 'iuweb',
        'adapter' => '%cache_encryption_adapter%',
        'options' => [
            'algo' => '%cache_encryption_algo%',
            'mode' => '%cache_encryption_mode%',
        ],
        'secrets' => [
            'node' => '%cache_encryption_secret_iu%',
            'shared' => '%cache_encryption_secret_shared%',
        ],
    ],

    'query_cache' => [
        // whether the cqrs cache is enabled
        'enabled' => '%cqrs_cache_enabled%',
        //sets the ttl for cqrs cache - note that these caches are also used by selfserve
        'ttl' => [
            \Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface::class => '%cqrs_cache_medium_ttl%',
            \Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface::class => '%cqrs_cache_long_ttl%',
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
    'html-purifier-cache-dir' => '/var/tmp/htmlPurifierCache',

    'auth' => [
        'user_unique_id_salt' => '%user_unique_id_salt%',
        'realm' => 'internal',
        'session_name' => 'Identity',
        'identity_provider' => \Common\Rbac\JWTIdentityProvider::class
    ],
];

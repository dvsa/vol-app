<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
    'olcs-doctrine' => [
        // Encryption key is provided by AWS Secrets Manager in deployed environments
        'encryption_key' => ''
    ],
    'doctrine' => [
        'configuration' => [
            'orm_default' => [
                // If running as CLI then use different directory to avoid permissions problems
                'proxy_dir'         => 'data/cache/DoctrineORMModule',
                'proxy_namespace'   => 'Dvsa\Olcs\Api\Entity\Proxy',
                'datetime_functions' => [
                    'date'          => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'time'          => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'timestamp'     => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'convert_tz'    => \Oro\ORM\Query\AST\Functions\DateTime\ConvertTz::class,
                ],
                'numeric_functions' => [
                    'timestampdiff' => \Oro\ORM\Query\AST\Functions\Numeric\TimestampDiff::class,
                    'dayofyear'     => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'dayofmonth'    => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'dayofweek'     => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'week'          => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'day'           => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'hour'          => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'minute'        => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'month'         => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'quarter'       => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'second'        => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'year'          => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'sign'          => \Oro\ORM\Query\AST\Functions\Numeric\Sign::class,
                    'pow'           => \Oro\ORM\Query\AST\Functions\Numeric\Pow::class,
                ],
                'string_functions'  => [
                    'md5'           => \Oro\ORM\Query\AST\Functions\SimpleFunction::class,
                    'group_concat'  => \Oro\ORM\Query\AST\Functions\String\GroupConcat::class,
                    'cast'          => \Oro\ORM\Query\AST\Functions\Cast::class,
                    'concat_ws'     => \Oro\ORM\Query\AST\Functions\String\ConcatWs::class,
                    'replace'       => \Oro\ORM\Query\AST\Functions\String\Replace::class,
                    'date_format'   => \Oro\ORM\Query\AST\Functions\String\DateFormat::class,
                    'ifnull'        => \DoctrineExtensions\Query\Mysql\IfNull::class,
                ],
            ],
        ],

    ],
    'document_share' => [
        'http' => [
            'adapter' => Laminas\Http\Client\Adapter\Curl::class,
            'curloptions' => [
                CURLOPT_TIMEOUT => 180,
            ],
        ],
        'path' => 'documents/'
            . '{Category}/{SubCategory}/{Date:Y}/{Date:m}/{Date:YmdHisu}_{Context}_{Description}.{Extension}'
    ],
    'email' => [
        'http' => [
            'adapter' => Laminas\Http\Client\Adapter\Curl::class,
        ],
    ],
    'companies_house' => [
        'http' => [
            'adapter' => Laminas\Http\Client\Adapter\Curl::class,
        ],
    ],
    'soflomo_purifier' => [
        'config' => [
            'Cache.SerializerPath' => sys_get_temp_dir(),
        ],
    ],
    'ebsr' => [
        'max_schema_errors' => 3, //maximum number of xml schema problems to return (prevents massive error messages)
        'transxchange_schema_version' => 2.5 //validate against transxchange schema (2.1, 2.4 and 2.5 available)
    ],
    'nr' => [
        'max_schema_errors' => 10, //maximum number of xml schema problems to return (prevents massive error messages)
        'compliance_episode' => [
            'xmlNs' => 'https://webgate.ec.testa.eu/move-hub/erru/',
            'erruVersion' => "3.4",
        ],
    ]
];

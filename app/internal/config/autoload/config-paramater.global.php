<?php

use Dvsa\LaminasConfigCloudParameters\Cast\Boolean;
use Dvsa\LaminasConfigCloudParameters\Cast\Integer;
use Dvsa\LaminasConfigCloudParameters\ParameterProvider\Aws\SecretsManager;
use Dvsa\LaminasConfigCloudParameters\ParameterProvider\Aws\ParameterStore;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\CacheableMediumTermQueryInterface;

$environment = getenv('ENVIRONMENT_NAME');

// This logic will be moved to environment variables with the migration to ECS.
$isProduction = strtoupper($environment) === 'APP';
$isProductionAccount = in_array(strtoupper($environment), ['INT', 'PP', 'APP']);

$providers = [];

if (!empty($environment)) {
    // The `int` environment is actually `nduint` in AWS Secrets Manager.
    $secretsManagerEnvironmentName = ($environment === 'int' ? 'nduint' : $environment);

    $providers = [
        SecretsManager::class => [
            sprintf('%sAPP%s-BASE-SM-APPLICATION-INTERNAL', ($isProductionAccount ? "" : "DEV"), ($isProduction ? "" : strtoupper($secretsManagerEnvironmentName))),
        ],
        ParameterStore::class => [
            sprintf('/applicationparams/%s/', strtolower($environment)),
        ],
    ];
}

return [
    'aws' => [
        'global' => [
            'http'    => [
                'connect_timeout' => 5,
                'timeout'         => 5,
            ],
        ],
    ],
    'config_parameters' => [
        'providers' => $providers,
    ],
    'casts' => [
        '[query_cache][enabled]' => Boolean::class,
        '[query_cache][ttl][' . CacheableMediumTermQueryInterface::class . ']' => Integer::class,
        '[query_cache][ttl][' . CacheableLongTermQueryInterface::class . ']' => Integer::class,
    ],
];

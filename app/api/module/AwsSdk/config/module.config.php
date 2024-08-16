<?php

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Dvsa\Olcs\AwsSdk\Factories\CognitoIdentityProviderClientFactory;
use Dvsa\Olcs\AwsSdk\Factories\S3ClientFactory;
use Dvsa\Olcs\AwsSdk\Factories\SqsClientFactory;

return [
    'service_manager' => [
        'factories' => [
            'S3Client' => S3ClientFactory::class,
            'SqsClient' => SqsClientFactory::class,
            CognitoIdentityProviderClient::class => CognitoIdentityProviderClientFactory::class,
        ],
    ]
];

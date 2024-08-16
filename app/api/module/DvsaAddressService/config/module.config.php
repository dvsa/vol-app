<?php

return [
    'service_manager' => [
        'factories' => [
            \Dvsa\Olcs\DvsaAddressService\Client\DvsaAddressServiceClient::class => \Dvsa\Olcs\DvsaAddressService\Client\DvsaAddressServiceClientFactory::class,
            \Dvsa\Olcs\DvsaAddressService\Service\DvsaAddressService::class => \Dvsa\Olcs\DvsaAddressService\Service\DvsaAddressServiceFactory::class,
        ],
        'aliases' => [
            'AddressService' => \Dvsa\Olcs\DvsaAddressService\Service\DvsaAddressService::class,
        ],
    ],
];

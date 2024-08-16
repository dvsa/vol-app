<?php

return [
    'service_manager' => [
        'factories' => [
            \Dvsa\Olcs\AcquiredRights\Client\AcquiredRightsClient::class => \Dvsa\Olcs\AcquiredRights\Client\AcquiredRightsClientFactory::class,
            \Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsService::class => \Dvsa\Olcs\AcquiredRights\Service\AcquiredRightsServiceFactory::class,
        ],
    ],
];

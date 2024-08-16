<?php

use Dvsa\Olcs\CompaniesHouse\Service\Client;
use Dvsa\Olcs\CompaniesHouse\Service\ClientFactory;

return [
    'companies_house' => [
        'http' => [
            'sslcapath' => '/etc/ssl/certs',
            'sslverifypeer' => true,
        ],
        'auth' => [
            'username' => 'changeme',
            'password' => '',
        ],
        'client' => [
            'baseuri' => 'https://api.companieshouse.gov.uk/',
        ],
    ],
    'service_manager' => [
        'factories' => [
            Client::class => ClientFactory::class,
        ],
    ],
];

<?php

return [
    'document_share' => [
        'http' => [],
        'client' => [
            'workspace' => '',
            'username' => '',
            'password' => '',
            'webdav_baseuri' => '',
        ]
    ],
    'service_manager' => [
        'factories' => [
            \Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser::class
                => \Dvsa\Olcs\DocumentShare\Service\S3BucketBrowserFactory::class,
        ],
    ]
];

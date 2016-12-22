<?php

return [
    'application-name' => 'selfserve',
    'cqrs_client' => [
        'adapter' => \Common\Service\Cqrs\Adapter\Curl::class,
        'timeout' => 60,
    ],
];

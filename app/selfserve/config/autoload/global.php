<?php

return [
    'application-name' => 'selfserve',
    'cqrs_client' => [
        'adapter' => \Common\Service\Cqrs\Adapter\Curl::class,
        'timeout' => 60,
    ],
    //  CSFR Form settings
    'csrf' => [
        'timeout' => 5400,  //  90 min; should match to auth timeout
    ],
];

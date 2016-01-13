<?php

return [
    'application-name' => 'selfserve',
    'cqrs_client' => [
        'adapter' => \Zend\Http\Client\Adapter\Curl::class,
        'timeout' => 60,
    ],
    'form_row' => [
        'render_date_hint' => true
    ],
];

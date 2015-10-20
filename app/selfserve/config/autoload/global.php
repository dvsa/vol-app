<?php

return [
    'application-name' => 'selfserve',
    'cqrs_client' => [
        'adapter' => \Zend\Http\Client\Adapter\Curl::class,
        'timeout' => 60,
    ],
    'view_manager' => array(
        'template_map' => array(
            // Dev versions of 404 and error
            'error/403' => __DIR__ . '/../../module/Olcs/view/error/404.phtml',
        ),
    ),
];

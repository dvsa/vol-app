<?php

return array(
    'application-name' => 'internal',
    'cqrs_client' => [
        'adapter' => \Zend\Http\Client\Adapter\Curl::class,
        'timeout' => 60,
    ],
    'soflomo_purifier' => [
        'config' => [
            'Cache.SerializerPath' => sys_get_temp_dir(),
        ],
    ],
    'zfc_rbac' => require('zfc_rbac.config.php')
);

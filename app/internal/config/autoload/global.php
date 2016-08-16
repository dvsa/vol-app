<?php

return array(
    'application-name' => 'internal',
    'cqrs_client' => [
        'adapter' => \Zend\Http\Client\Adapter\Curl::class,
        // This timeout value is too large probably should be 60, but the document store on skyscape is very slow
        'timeout' => 120,
    ],
    'form_row' => [
        'render_date_hint' => false
    ],
    'soflomo_purifier' => [
        'config' => [
            'Cache.SerializerPath' => sys_get_temp_dir(),
        ],
    ],
    'zfc_rbac' => require('zfc_rbac.config.php')
);

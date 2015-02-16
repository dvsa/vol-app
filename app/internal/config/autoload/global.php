<?php

return array(
    'version' => (file_exists('../version') ? file_get_contents('../version'): ''),
    'service_api_mapping' => array(
        array(
            'endpoint' => 'http://olcspayment.dev/api/',
            'apis' => array(
                'Vosa\Payment\Token' => 'token',
                'Vosa\Payment\Db' => 'paymentdb',
                'Vosa\Payment\Card' => 'cardpayment',
            ),
        ),
        array(
            'endpoint' => 'http://olcs-backend/',
            'apis' => array(
                'User' => 'user',
                'Person' => 'person',
            )
        )
    ),
    'application-name' => 'internal',
    /**
     * @todo Not sure if there is a better place to do this, but I essentially need to override the common controller
     * namespace to extend the behaviour. And need to override the common service manager factories
     */
    'controllers' => array(
        'invokables' => array(
            'Common\Controller\Application\VehicleSafety\SafetyController' =>
                'Olcs\Controller\Journey\Application\VehicleSafety\SafetyController',
            'Common\Controller\Application\VehicleSafety\VehicleController' =>
                'Olcs\Controller\Journey\Application\VehicleSafety\VehicleController',
        )
    ),

);

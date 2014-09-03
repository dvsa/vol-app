<?php

return array(
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
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'section.vehicle-safety.vehicle.formatter.vrm' => function ($serviceManager) {
                return new \Olcs\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm();
            }
        )
    )
);

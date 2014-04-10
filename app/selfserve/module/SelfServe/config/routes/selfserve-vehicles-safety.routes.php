<?php

return [
    'vehicles-safety' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:licenceId/vehicle-safety/:step',
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Index',
                'action' => 'index'
            )
        ),
    ),
    'vehicles-safety-complete' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:licenceId/vehicles-safety/complete',
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Index',
                'action' => 'complete'
            )
        ),
    ),
    'vehicle-edit' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:licenceId/vehicle/:vehicleId/edit',
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Vehicle',
                'action' => 'edit'
            )
        ),
    ),
];


<?php

return [
    'vehicles-safety' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/vehicle-safety/:step',
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Index',
                'action' => 'index',
                'step'  => 'index',
            )
        ),    
        'may_terminate' => true,
        'child_routes' => array(
            'vehicles-safety-action' => array(
                'type' => 'segment',
                'options' => array(
                        'route' => '/vehicle[/:action][/:vehicleId]',
                        'constraints' => array(
                                'vehicleId' => '[0-9]+'
                        ),
                        'defaults' => array(
                                'controller' => 'SelfServe\VehiclesSafety\VehicleController'
                        ),
                ),
            ),
        ),
    ),
    'vehicle-action' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/vehicle',
            'constraints' => array(
                    'applicationId' => '[0-9]+'
            ),
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Vehicle',
                'action'    => 'index'
            )
        ), 
        'may_terminate' => false,
        'child_routes' => array(
            'vehicle-add' => array(
                'type' => 'segment',
                'options' => array(
                        'route' => '/add',
                        'defaults' => array(
                                'controller' => 'SelfServe\VehiclesSafety\Vehicle',
                                'action'    => 'add'
                        ),
                ),
            ),
            'vehicle-edit' => array(
                'type' => 'segment',
                'options' => array(
                        'route' => '/:vehicleId/edit',
                        'constraints' => array(
                                'vehicleId' => '[0-9]+'
                        ),
                        'defaults' => array(
                                'controller' => 'SelfServe\VehiclesSafety\Vehicle',
                                'action'    => 'edit'
                        ),
                ),
            ),
        ),
    ),
    
];


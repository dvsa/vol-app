<?php

return [
    'vehicle-safety' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/vehicle-safety[/]',
            'constraints' => array(
                'applicationId' => '[0-9]+'
            ),
            'defaults' => array(
                'controller' => 'Selfserve\VehiclesSafety\Index',
                'action' => 'index',
                'step' => 'index'
            )
        ),
        'may_terminate' => true,
        'child_routes' => array(
            'safety-action' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'safety',
                    'defaults' => array(
                        'controller' => 'SelfServe\VehiclesSafety\Safety',
                        'action' => 'index'
                    )
                )
            ),
            'vehicle-action' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => 'vehicle'
                ),
                'may_terminate' => false,
                'child_routes' => array(
                    'vehicle-add' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/add',
                            'defaults' => array(
                                'controller' => 'SelfServe\VehiclesSafety\Vehicle',
                                'action' => 'add'
                            )
                        )
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
                                'action' => 'edit'
                            )
                        )
                    ),
                    'vehicle-delete' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/:vehicleId/delete',
                            'constraints' => array(
                                'vehicleId' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'SelfServe\VehiclesSafety\Vehicle',
                                'action' => 'delete'
                            )
                        )
                    )
                )
            )
        )
    )
];

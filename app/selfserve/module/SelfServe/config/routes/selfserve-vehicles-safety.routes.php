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
                'controller' => 'Selfserve\VehicleSafety\Index',
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
                        'controller' => 'SelfServe\VehicleSafety\Safety',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'workshop' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/workshop/:action[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'SelfServe\VehicleSafety\Workshop'
                            )
                        )
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
                                'controller' => 'SelfServe\VehicleSafety\Vehicle',
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
                                'controller' => 'SelfServe\VehicleSafety\Vehicle',
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
                                'controller' => 'SelfServe\VehicleSafety\Vehicle',
                                'action' => 'delete'
                            )
                        )
                    )
                )
            )
        )
    )
];

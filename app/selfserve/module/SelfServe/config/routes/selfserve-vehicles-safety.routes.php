<?php

return array(
    'vehicle-safety' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/vehicle-safety',
            'constraints' => array(
                'applicationId' => '[0-9]+'
            ),
            'defaults' => array(
                'controller' => 'Selfserve\VehicleSafety\Index',
                'action' => 'index',
                'step' => 'index'
            )
        ),
        'may_terminate' => false,
        'child_routes' => array(
            'safety' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/safety',
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
            'vehicle' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/vehicle[/:action][/:id]',
                    'constraints' => array(
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Selfserve\VehicleSafety\Vehicle',
                        'action' => 'index'
                    )
                )
            )
        )
    )
);

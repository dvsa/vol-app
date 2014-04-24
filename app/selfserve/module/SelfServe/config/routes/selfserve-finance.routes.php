<?php

return [
    'finance' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/finance[/][:step]',
            'defaults' => array(
                'controller' => 'Selfserve\Finance\Index',
                'action' => 'index',
                'step'  => 'index',
            ),
        ),
        'may_terminate' => true,
        'child_routes' => array(
                'operating_centre_action' => array(
                        'type' => 'segment',
                        'options' => array(
                                'route' => '/operating-centre[/:action][/:operatingCentreId]',
                                'constraints' => array(
                                        'operatingCentreId' => '[0-9]+'
                                ),
                                'defaults' => array(
                                        'controller' => 'SelfServe\Finance\OperatingCentreController'
                                ),
                        ),
                ),
        ),
    ),
];

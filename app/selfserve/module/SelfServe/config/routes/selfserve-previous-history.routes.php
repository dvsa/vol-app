<?php

return [
    'previous-history' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/previous-history/:step',
            'defaults' => array(
                'controller' => 'SelfServe\PreviousHistory\Index',
                'action' => 'generateStepForm'
            )
        ),
    ),
    'criminal-convictions' => array(
        'type' => 'segment',
        'options' => array(
            'route' => '/:applicationId/previous-history/convictions-penalties[/:action][/:id][/]',
            'constraints' => array(
                'id' => '[0-9]+'
            ),
            'defaults' => array(
                'controller' => 'SelfServe\PreviousHistory\ConvictionsAndPenalties',
                'action' => 'index'
            )
        ),
    ),
];

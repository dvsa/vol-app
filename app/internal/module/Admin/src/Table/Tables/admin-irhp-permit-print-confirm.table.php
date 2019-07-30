<?php

return [
    'variables' => [
        'title' => 'Printer must be loaded with the templates for the following Permit Numbers and in this order.',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'print' => [
                    'requireRows' => false,
                    'label' => 'Confirm',
                    'class' => 'action--primary'
                ],
                'cancel' => [
                    'requireRows' => false,
                    'class' => 'action--secondary'
                ]
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Sequence Number',
            'name' => 'sequenceNumber'
        ],
        [
            'title' => 'Permit Number',
            'name' => 'permitNumberWithPrefix'
        ],
        [
            'title' => 'Emissions Category',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => 'StackValue',
        ],
        [
            'title' => 'Operator Name',
            'formatter' => 'IrhpPermitOrganisationName'
        ],
    ]
];

<?php

return [
    'settings' => [
        'crud' => [
            'actions' => [
                'confirm' => [
                    'requireRows' => true,
                    'label' => 'Continue',
                    'class' => 'action--primary js-require--multiple'
                ],
                'cancel' => [
                    'requireRows' => false,
                    'class' => 'action--secondary'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ],
        ],
        'row-disabled-callback' => function ($row) {
            return in_array(
                $row['status']['id'],
                [
                    Common\RefData::IRHP_PERMIT_STATUS_AWAITING_PRINTING,
                    Common\RefData::IRHP_PERMIT_STATUS_PRINTING,
                ]
            );
        },
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Permit Number',
            'name' => 'permitNumberWithPrefix'
        ],
        [
            'title' => 'Operator Name',
            'formatter' => 'IrhpPermitOrganisationName'
        ],
        [
            'title' => 'Application Ref',
            'formatter' => 'IrhpPermitApplicationRefLink'
        ],
        [
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'RefData',
        ],
        [
            'type' => 'Checkbox',
            'width' => 'checkbox',
            'disableIfRowIsDisabled' => true,
        ],
    ]
];

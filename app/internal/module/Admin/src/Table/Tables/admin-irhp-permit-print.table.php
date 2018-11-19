<?php

return [
    'settings' => [
        'crud' => [
            'actions' => [
                'print' => [
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
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
            'formatter' => function ($data) {
                return $data['irhpPermitApplication']['ecmtPermitApplication']['licence']['organisation']['name'];
            }
        ],
        [
            'title' => 'Application Ref',
            'formatter' => function ($data) {
                return sprintf(
                    '<a href="%s">%s</a>',
                    $this->generateUrl(
                        [
                            'action' => 'index',
                            'licence' => $data['irhpPermitApplication']['ecmtPermitApplication']['licence']['id']
                        ],
                        'licence/permits',
                        false
                    ),
                    $data['irhpPermitApplication']['ecmtPermitApplication']['applicationRef']
                );
            }

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

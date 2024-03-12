<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'IRFO PSV Authorisations'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'renew' => [
                    'label' => 'Set to renew',
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => true
                ],
                'print' => [
                    'label' => 'Print checklist',
                    'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                    'requireRows' => true
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Auth Id',
            'isNumeric' => true,
            'formatter' => fn($data) => sprintf(
                '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id'], 'organisation' => $data['organisation']['id']],
                    'operator/irfo/psv-authorisations',
                    false
                ),
                $data['id']
            )
        ],
        [
            'title' => 'Operator',
            'formatter' => fn($data) => sprintf(
                '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                $this->generateUrl(
                    ['action' => 'edit', 'organisation' => $data['organisation']['id']],
                    'operator/irfo/details',
                    false
                ),
                $data['organisation']['name']
            )
        ],
        [
            'title' => 'In force date',
            'name' => 'inForceDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'Expiry date',
            'name' => 'expiryDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data) => $data['status']['description']
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data) => $data['irfoPsvAuthType']['description']
        ],
        [
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ],
    ]
];

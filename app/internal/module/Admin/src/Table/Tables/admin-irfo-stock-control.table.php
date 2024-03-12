<?php

return [
    'variables' => [
        'titleSingular' => 'IRFO permit',
        'title' => 'IRFO permits'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'in-stock' => [
                    'label' => 'In Stock', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true
                ],
                'issued' => ['class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true],
                'void' => ['class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true],
                'returned' => ['class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
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
            'title' => 'Serial number',
            'isNumeric' => true,
            'name' => 'serialNo',
        ],
        [
            'title' => 'Permit No',
            'isNumeric' => true,
            'formatter' => function ($data) {
                if (empty($data['irfoGvPermit']['id'])) {
                    return '';
                }

                return sprintf(
                    '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                    $this->generateUrl(
                        [
                            'action' => 'details',
                            'id' => $data['irfoGvPermit']['id'],
                            'organisation' => $data['irfoGvPermit']['organisation']['id']
                        ],
                        'operator/irfo/gv-permits',
                        false
                    ),
                    $data['irfoGvPermit']['id']
                );
            }
        ],
        [
            'title' => 'Operator',
            'formatter' => function ($data) {
                if (empty($data['irfoGvPermit']['organisation']['id'])) {
                    return '';
                }

                return sprintf(
                    '<a class="govuk-link" href="%s">%s</a>',
                    $this->generateUrl(
                        [
                            'organisation' => $data['irfoGvPermit']['organisation']['id']
                        ],
                        'operator/irfo/gv-permits',
                        false
                    ),
                    $data['irfoGvPermit']['organisation']['name']
                );
            }
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data) => $data['status']['description']
        ],
        [
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ],
    ]
];

<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'titleSingular' => 'Impounding',
        'title' => 'Impoundings',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'generate' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Application received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'impounding' => $data['id']],
                    'case_details_impounding',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'applicationReceiptDate'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data, $column) => $this->translator->translate($data['impoundingType']['id'])
        ],
        [
            'title' => 'Presiding TC/DTC/HTRU/DHTRU',
            'formatter' => fn($data) => $data['presidingTc']['name'] ?? ''
        ],
        [
            'title' => 'Outcome',
            'formatter' => fn($data, $column) => isset($data['outcome']['id']) ? $this->translator->translate($data['outcome']['id']) : ''
        ],
        [
            'title' => 'Outcome sent',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'outcomeSentDate'

        ],
    ]
];

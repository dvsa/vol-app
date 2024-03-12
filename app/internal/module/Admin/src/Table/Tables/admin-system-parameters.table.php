<?php

use Common\Service\Table\Formatter\SystemParameterLink;

return [
    'variables' => [
        'title' => 'parameters',
        'titleSingular' => 'parameter'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'columns' => [
        [
            'title' => 'Key',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
            'formatter' => SystemParameterLink::class
        ],
        [
            'title' => 'Value',
            'name' => 'paramValue',
            'sort' => 'paramValue',
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

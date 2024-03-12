<?php

return [
    'variables' => [
        'titleSingular' => 'Recipient',
        'title' => 'Recipients'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'edit' => ['class' => 'govuk-button govuk-button--secondary js-require--one', 'requireRows' => true],
                'delete' => ['class' => 'govuk-button govuk-button--warning js-require--one', 'requireRows' => true]
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
            'title' => 'Contact Name',
            'name' => 'contactName',
            'sort' => 'contactName',
        ],
        [
            'title' => 'Email',
            'name' => 'emailAddress',
            'sort' => 'emailAddress',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

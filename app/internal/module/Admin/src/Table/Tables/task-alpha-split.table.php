<?php

use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'titleSingular' => 'Alpha split',
        'title' => 'Alpha splits',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'addAlphasplit' => ['class' => 'govuk-button', 'label' => 'add'],
                'editAlphasplit' => [
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'edit',
                    'requireRows' => true
                ],
                'deleteAlphasplit' => [
                    'class' => 'govuk-button govuk-button--warning js-require--multiple',
                    'label' => 'delete',
                    'requireRows' => true
                ]
            ]
        ],
        // This has to exist so that the title gets prepended with the document count
        'paginate' => [
        ]
    ],

    'columns' => [
        [
            'title' => 'User',
            'name' => 'user->contactDetails->person',
            'formatter' => Name::class,
        ],
        [
            'title' => 'Assign operator tasks starting with these letters',
            'formatter' => fn($data) => $data['letters']
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

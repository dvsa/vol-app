<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Replacements',
        'titleSingular' => 'Replacement',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'edit' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Id',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Placeholder',
            'name' => 'placeholder',
            'sort' => 'placeholder',
            'formatter' => fn($row) =>
                // Replaces standard curly braces with html entity codes to avoid table helper variable replacement
                str_replace(['{', '}'], ['&#123;', '&#125;'], Escape::html($row['placeholder'])),
        ],
        [
            'title' => 'Replacement Text',
            'name' => 'replacementText',
            'sort' => 'replacementText',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

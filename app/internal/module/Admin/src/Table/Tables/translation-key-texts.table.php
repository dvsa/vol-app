<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Key Translations',
        'titleSingular' => 'Key Translation',
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
                'edittexts' => [
                    'class' => 'govuk-button',
                    'requireRows' => false,
                    'label' => 'Edit Translation Key',
                ],
                'deleteText' => [
                    'label' => 'Delete Text',
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--secondary js-require--one'
                ]
            ],
        ],
    ],

    'columns' => [
        [
            'title' => 'Language',
            'formatter' => fn($row) => Escape::html($row['language']['name']),
        ],
        [
            'title' => 'Translated Text',
            'name' => 'translatedText',
        ],
        [
            'title' => 'Edited date',
            'name' => 'lastModifiedOn',
            'sort' => 'lastModifiedOn',
            'formatter' => fn($row, $column) => empty($row['lastModifiedOn'])
                ? date('d/m/Y', strtotime($row['createdOn']))
                : date('d/m/Y', strtotime($row['lastModifiedOn']))
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

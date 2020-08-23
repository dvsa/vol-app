<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Partial Markup Translations',
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
                'editpartial' => [
                    'class' => 'action--primary',
                    'requireRows' => false,
                    'label' => 'Edit Partial Markup',
                ],
            ],
        ],
    ],

    'columns' => [
        [
            'title' => 'Language',
            'formatter' => function ($row) {
                return Escape::html($row['language']['name']);
            },
        ],
        [
            'title' => 'Edited date',
            'name' => 'lastModifiedOn',
            'sort' => 'lastModifiedOn',
            'formatter' => function ($row, $column) {
                return empty($row['lastModifiedOn'])
                    ? date('d/m/Y', strtotime($row['createdOn']))
                    : date('d/m/Y', strtotime($row['lastModifiedOn']));
            }
        ],
    ]
];

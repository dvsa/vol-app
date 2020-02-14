<?php

use Common\Util\Escape;

return [
    'variables' => [
        'title' => 'Key Translations',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
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
            'title' => 'Translated Text',
            'name' => 'translatedText',
        ],
    ]
];

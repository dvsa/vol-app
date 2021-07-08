<?php

declare(strict_types=1);

return [
    'variables' => [
        'title' => 'Bus notice periods',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'action--primary',
                    'requireRows' => false,
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Rules',
            'name' => 'noticeArea',
        ],
        [
            'title' => 'Notice period',
            'name' => 'standardPeriod',
        ],
    ],
];

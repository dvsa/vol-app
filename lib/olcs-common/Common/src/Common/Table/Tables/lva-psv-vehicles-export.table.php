<?php

use Common\Service\Table\Formatter\Date;

$translationPrefix = 'application_vehicle-safety_vehicle-psv.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'titleSingular' => $translationPrefix . '.title.singular',
        'empty_message' => $translationPrefix . '.empty_message',
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'name' => 'vrm',
        ],
        [
            'title' => $translationPrefix . '.make',
            'name' => 'makeModel',
        ],
        [
            'title' => $translationPrefix . '.specified',
            'name' => 'specifiedDate',
            'formatter' => Date::class,
        ],
        [
            'title' => $translationPrefix . '.removed',
            'name' => 'removalDate',
            'formatter' => Date::class,
        ]
    ]
];

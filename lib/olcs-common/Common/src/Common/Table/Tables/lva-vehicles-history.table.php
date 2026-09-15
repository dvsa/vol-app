<?php

$translationPrefix = 'application_vehicle-safety_vehicle-history.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title'
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.licence',
            'name' => 'licenceNo'
        ],
        [
            'title' => $translationPrefix . '.specified',
            'name' => 'specifiedDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class
        ],
        [
            'title' => $translationPrefix . '.removed',
            'name' => 'removalDate',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class
        ],
        [
            'title' => $translationPrefix . '.disc-no',
            'name' => 'discNo'
        ]
    ]
];

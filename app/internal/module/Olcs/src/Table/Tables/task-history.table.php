<?php

use Common\Service\Table\Formatter\EventHistoryUser;
use Common\Service\Table\Formatter\StackValue;

return [
    'variables' => [
        'title' => 'History'
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Details',
            'stack' => 'eventHistoryType->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'Info',
            'name' => 'eventData',
        ],
        [
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
        ],
        [
            'title' => 'By',
            'formatter' => EventHistoryUser::class,
        ],
    ]
];

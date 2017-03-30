<?php

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Details',
            'stack' => 'eventHistoryType->description',
            'formatter' => 'StackValue',
        ],
        [
            'title' => 'Info',
            'name' => 'eventData',
        ],
        [
            'title' => 'Date',
            'name' => 'eventDatetime',
            'formatter' => 'DateTime',
        ],
        [
            'title' => 'By',
            'formatter' => 'eventHistoryUser',
        ],
    ]
];

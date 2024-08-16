<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValueReplacer;

return [
    'variables' => [
        'title' => 'Access history'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Date',
            'name' => 'createdOn',
            'formatter' => Date::class
        ],
        [
            'title' => 'User',
            'formatter' => StackValueReplacer::class,
            'stringFormat' => '{user->contactDetails->person->forename} {user->contactDetails->person->familyName}'
        ]
    ]
];

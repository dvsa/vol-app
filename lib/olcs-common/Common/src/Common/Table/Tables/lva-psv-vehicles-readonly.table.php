<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValue;

$translationPrefix = 'application_vehicle-safety_vehicle-psv.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'vehicle',
        'within_form' => true
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50]
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'stack' => 'vehicle->vrm',
            'formatter' => StackValue::class,
            'sort' => 'v.vrm'
        ],
        [
            'title' => $translationPrefix . '.make',
            'stack' => 'vehicle->makeModel',
            'formatter' => StackValue::class,
        ],
        [
            'title' => $translationPrefix . '.specified',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.removed',
            'formatter' => Date::class,
            'name' => 'removalDate',
            'sort' => 'removalDate'
        ]
    ]
];

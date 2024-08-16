<?php

use Common\Service\Table\Formatter\DataRetentionRuleActionType;
use Common\Service\Table\Formatter\DataRetentionRuleLink;

return [
    'variables' => [
        'title' => 'Data Retention rules',
        'titleSingular' => 'Data retention rule',
        'titlePlural' => 'Data retention rules',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'columns' => [
        [
            'title' => 'ID',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
            'formatter' => DataRetentionRuleLink::class
        ],
        [
            'title' => 'Action type',
            'name' => 'actionType',
            'sort' => 'actionType',
            'formatter' => DataRetentionRuleActionType::class
        ],
    ]
];

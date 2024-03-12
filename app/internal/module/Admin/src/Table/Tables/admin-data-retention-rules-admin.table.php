<?php

use Common\Service\Table\Formatter\DataRetentionRuleActionType;
use Common\Service\Table\Formatter\DataRetentionRuleAdminLink;
use Common\Service\Table\Formatter\DataRetentionRuleIsEnabled;

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
            'formatter' => DataRetentionRuleAdminLink::class
        ],
        [
            'title' => 'Retention period',
            'isNumeric' => true,
            'name' => 'retentionPeriod',
            'sort' => 'retentionPeriod',
        ],
        [
            'title' => 'Max data set',
            'name' => 'maxDataSet',
            'sort' => 'maxDataSet',
        ],
        [
            'title' => 'is Enabled',
            'name' => 'isEnabled',
            'sort' => 'isEnabled',
            'formatter' => DataRetentionRuleIsEnabled::class
        ],
        [
            'title' => 'Action type',
            'name' => 'actionType',
            'sort' => 'actionType',
            'formatter' => DataRetentionRuleActionType::class
        ],
    ]
];

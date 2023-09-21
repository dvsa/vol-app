<?php

use Common\Service\Table\Formatter\DataRetentionAssignedTo;
use Common\Service\Table\Formatter\DataRetentionRecordLink;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Data Retention Records',
        'titleSingular' => 'Data retention record',
        'titlePlural' => 'Data retention records',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'assign' => [
                    'label' => 'Assign',
                    'requireRows' => true,
                    'class' => 'govuk-button js-require--multiple'
                ],
                'delay' => [
                    'label' => 'Delay',
                    'requireRows' => true,
                    'class' => 'govuk-button js-require--multiple'
                ],
                'review' => [
                    'label' => 'Mark for Review',
                    'requireRows' => true,
                    'class' => 'govuk-button js-require--multiple'
                ],
                'delete' => [
                    'label' => 'Mark as Delete',
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--warning js-require--multiple'
                ],
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'columns' => [
        [
            'title' => 'Description',
            'formatter' => DataRetentionRecordLink::class,
            'sort' => 'licNo',
        ],
        [
            'title' => 'Date added',
            'name' => 'createdOn',
            'formatter' => Date::class,
            'sort' => 'createdOn',
        ],
        [
            'title' => 'Next review date',
            'name' => 'nextReviewDate',
            'formatter' => Date::class,
            'sort' => 'nextReviewDate'
        ],
        [
            'title' => 'Assigned to',
            'formatter' => DataRetentionAssignedTo::class,
            'sort' => 'p.forename'
        ],
        [
            'title' => 'Select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ],
    ],
];

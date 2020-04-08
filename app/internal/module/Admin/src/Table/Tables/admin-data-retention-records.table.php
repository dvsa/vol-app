<?php

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
                    'class' => 'action--primary js-require--multiple'
                ],
                'delay' => [
                    'label' => 'Delay',
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
                ],
                'review' => [
                    'label' => 'Mark for Review',
                    'requireRows' => true,
                    'class' => 'action--primary js-require--multiple'
                ],
                'delete' => [
                    'label' => 'Mark as Delete',
                    'requireRows' => true,
                    'class' => 'action--delete js-require--multiple'
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
            'formatter' => 'DataRetentionRecordLink',
            'sort' => 'id',
        ],
        [
            'title' => 'Date added',
            'name' => 'createdOn',
            'formatter' => 'Date',
            'sort' => 'createdOn',
        ],
        [
            'title' => 'Next review date',
            'name' => 'nextReviewDate',
            'formatter' => 'Date',
            'sort' => 'nextReviewDate'
        ],
        [
            'title' => 'Assigned to',
            'formatter' => 'DataRetentionAssignedTo',
            'sort' => 'assignedTo'
        ],
        [
            'title' => 'Select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ],
    ],
];

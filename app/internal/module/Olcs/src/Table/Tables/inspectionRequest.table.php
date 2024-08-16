<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\InspectionRequestId;

return [
    'variables' => [
        'title' => 'Inspection requests',
        'titleSingular' => 'Inspection request'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'formName' => 'inspectionReport',
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--warning js-require--one',
                    'label' => 'Remove'
                ]
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'ID',
            'isNumeric' => true,
            'sort' => 'id',
            'name' => 'id',
            'formatter' => InspectionRequestId::class
        ],
        [
            'title' => 'Report type',
            'formatter' => fn($row) => $row['reportType']['description'],
            'name' => 'reportType',
            'sort' => 'reportType'
        ],
        [
            'title' => 'Date requested',
            'name' => 'requestDate',
            'formatter' => Date::class,
            'sort' => 'requestDate'
        ],
        [
            'title' => 'Due date',
            'name' => 'dueDate',
            'formatter' => Date::class,
            'sort' => 'duetDate'
        ],
        [
            'title' => 'Application ID',
            'isNumeric' => true,
            'formatter' => fn($row) => $row['application']['id'],
            'name' => 'applicationId',
            'sort' => 'applicationId'
        ],
        [
            'title' => 'Result status',
            'formatter' => fn($row) => $row['resultType']['description'],
            'name' => 'resultType',
            'sort' => 'resultType'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ]
    ]
];

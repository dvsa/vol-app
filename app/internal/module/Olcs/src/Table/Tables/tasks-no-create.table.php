<?php

use Common\Service\Table\Formatter\TaskCheckbox;
use Common\Service\Table\Formatter\TaskDate;
use Common\Service\Table\Formatter\TaskDescription;
use Common\Service\Table\Formatter\TaskIdentifier;
use Common\Service\Table\Formatter\TaskOwner;

return [
    'variables' => [
        'title' => 'Tasks',
        'titleSingular' => 'Task',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'edit' => ['requireRows' => true, 'class' => 'govuk-button js-require--one'],
                're-assign task' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--multiple'],
                'close task' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--multiple']
            ]
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Link',
            'formatter' => TaskIdentifier::class,
            'name' => 'link',
            'sort' => 'linkDisplay',
        ],
        [
            'title' => 'Category',
            'name' => 'categoryName',
            'sort' => 'categoryName',
        ],
        [
            'title' => 'Sub category',
            'class' => 'no-wrap',
            'name' => 'taskSubCategoryName',
            'sort' => 'taskSubCategoryName',
        ],
        [
            'title' => 'Description',
            'formatter' => TaskDescription::class,
            'sort' => 'description',
        ],
        [
            'title' => 'Date',
            'name' => 'actionDate',
            'formatter' => TaskDate::class,
            'sort' => 'actionDate',
        ],
        [
            'title' => 'Owner',
            'formatter' => TaskOwner::class,
            'sort' => 'teamName,ownerName',
        ],
        [
            'title' => 'Name',
            'name' => 'name',
            'sort' => 'name',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'formatter' => TaskCheckbox::class,
        ]
    ]
];

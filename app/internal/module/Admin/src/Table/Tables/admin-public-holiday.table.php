<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\PublicHolidayArea;

return [
    'variables' => [
        'title' => 'Public holidays',
        'titleSingular' => 'Public holiday',
        'empty_message' => 'You don\'t have public holidays',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false,
                    'label' => 'Add holiday',
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'type' => 'Action',
            'action' => 'edit',
            'title' => 'Date',
            'name' => 'publicHolidayDate',
            'sort' => 'publicHolidayDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'Area',
            'formatter' => PublicHolidayArea::class,
        ],
        [
            'type' => 'Action',
            'action' => 'delete',
            'text' => 'Remove',
        ],
    ],
];

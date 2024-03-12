<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'titleSingular' => 'Person',
        'title' => 'People',
        'empty_message' => 'selfserve-app-subSection-your-business-people-other.table.empty-message',
        'required_label' => 'person',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true],
                'delete' => [
                    'label' => 'people_table_action.delete.label',
                    'class' => 'govuk-button govuk-button--warning',
                    'requireRows' => true,
                ],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnName',
            'type' => 'Action',
            'action' => 'edit',
            'formatter' => Name::class
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnHasOtherNames',
            'name' => 'otherName',
            'formatter' => fn($row) => $row['otherName'] ? 'Yes' : 'No'
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnDate',
            'name' => 'birthDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnPosition',
            'name' => 'position',
        ],
        [
            'title' => 'Disqual',
            'formatter' => DisqualifyUrl::class,
        ],
        [
            'name' => 'select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ]
    ]
];

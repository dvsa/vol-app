<?php

return [
    'variables' => [
        'id' => 'outstanding-applications',
        'title' => 'Outstanding applications',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'outstanding-applications']
        ],
    ],
    'settings' => [
        'crud' => [
            'formName' => 'outstanding-applications',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'outstanding-applications'
    ],
    'columns' => [
        [
            'title' => 'Application No',
            'formatter' => function ($data) {
                $string = \Common\Util\Escape::html($data['id']);
                if (isset($data['licNo'])) {
                    $string = \Common\Util\Escape::html($data['licNo']) . ' / ' . $string;
                }

                return $string;
            }
        ],
        [
            'title' => 'Application type',
            'name' => 'applicationType'
        ],
        [
            'title' => 'Received date',
            'name' => 'receivedDate'
        ],
        [
            'title' => 'OOO/OOR',
            'formatter' => function ($data, $column) {
                $string = ' - ';
                if (isset($data['ooo'])) {
                    $string = \Common\Util\Escape::html($data['ooo']) . $string;
                }
                if (isset($data['oor'])) {
                    $string .= \Common\Util\Escape::html($data['oor']);
                }
                return $string;
            }
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ]
    ]
];

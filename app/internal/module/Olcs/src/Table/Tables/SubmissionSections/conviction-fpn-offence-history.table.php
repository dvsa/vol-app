<?php

return [
    'variables' => [
        'id' => 'conviction-fpn-offence-history',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conviction-fpn-offence-history']
        ],
        'title' => 'Conviction / FPN / Offence history'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'conviction-fpn-offence-history',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'conviction-fpn-offence-history'
    ],
    'columns' => [
        [
            'title' => 'Date of conviction',
            'formatter' => function ($data) {
                if ($data['convictionDate'] == null) {
                    return 'N/A';
                }

                return $data['convictionDate'];
            },
            'name' => 'convictionDate'
        ],
        [
            'title' => 'Date of offence',
            'name' => 'offenceDate'
        ],
        [
            'title' => 'Name / defendant type',
            'formatter' => fn($data) => $data['name'] . '<br />' . $data['defendantType'],
            'name' => 'name'
        ],
        [
            'title' => 'Description',
            'name' => 'categoryText'
        ],
        [
            'title' => 'Court/FPN',
            'name' => 'court'
        ],
        [
            'title' => 'Penalty',
            'name' => 'penalty'
        ],
        [
            'title' => 'SI',
            'name' => 'msi'
        ],
        [
            'title' => 'Declared',
            'name' => 'isDeclared'
        ],
        [
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ],
    ]
];

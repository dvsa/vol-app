<?php
return [
    'variables' => [
        'id' => 'convictions-and-penalties',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-previous-history', 'subSection' => 'convictions-and-penalties']
        ],
        'title' => 'Convictions/penalties'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'convictions-and-penalties',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'convictions-and-penalties'
    ],
    'columns' => [
        [
            'title' => 'Offence',
            'name' => 'offence',
        ],
        [
            'title' => 'Conviction date',
            'name' => 'convictionDate'
        ],
        [
            'title' => 'Name of court',
            'name' => 'courtFpn'
        ],
        [
            'title' => 'Penalty',
            'name' => 'penalty',
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

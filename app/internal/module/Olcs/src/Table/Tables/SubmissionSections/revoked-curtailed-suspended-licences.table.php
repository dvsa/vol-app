<?php

return [
    'variables' => [
        'id' => 'revoked-curtailed-suspended-licences',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'tm-previous-history', 'subSection' => 'revoked-curtailed-suspended-licences']
        ],
        'title' => 'Revoked, curtailed or suspended licences'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'revoked-curtailed-suspended-licences',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'revoked-curtailed-suspended-licences'
    ],
    'columns' => [
        [
            'title' => 'Licence No.',
            'name' => 'licNo',
        ],
        [
            'title' => 'Licence holder name',
            'name' => 'holderName'
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

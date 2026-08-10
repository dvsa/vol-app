<?php

return [
    'variables' => [
        'id' => 'transport-managers',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'transport-managers']
        ],
        'title' => 'Transport managers'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'transport-managers',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'transport-managers'
    ],
    'columns' => [
        [
            'title' => 'Name',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['title'])
                . ' ' . \Common\Util\Escape::html($data['forename'])
                . ' ' . \Common\Util\Escape::html($data['familyName'])
        ],
        [
            'title' => 'DOB',
            'name' => 'birthDate'
        ],
        [
            'title' => 'Other Licences / Applications',
            'formatter' => function ($data) {
                $returnString = '';
                foreach ($data['otherLicences'] as $other) {
                    $returnString .= \Common\Util\Escape::html($other['licNo']);
                    $returnString .= $other['applicationId']
                        ? ' / ' . \Common\Util\Escape::html($other['applicationId'])
                        : '';
                    $returnString .= "<br />";
                }
                return $returnString;
            },
        ],
        [
            'title' => 'Qualifications',
            'formatter' => fn($data) => implode(
                ', ',
                array_map(\Common\Util\Escape::html(...), $data['qualifications'])
            )
        ],
        [
            'title' => 'Type',
            'name' => 'tmType'
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

<?php

use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'id' => 'oppositions',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'oppositions']
        ],
        'title' => 'Oppositions'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'oppositions',
            'actions' => [
                'refresh-table' => [
                    'label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false
                ],
                'delete-row' => [
                    'label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true
                ]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display',
    ],
    'attributes' => [
        'name' => 'oppositions'
    ],
    'columns' => [
        [
            'title' => 'Opposition type',
            'formatter' => fn($data) => $data['oppositionType'],
        ],
        [
            'title' => 'Date received',
            'name' => 'dateReceived',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a class="govuk-link" href="' . $this->generateUrl(
                    ['action' => 'edit', 'opposition' => $data['id']],
                    'case_opposition',
                    true
                ) . '">' . $data['dateReceived'] . '</a>',
        ],
        [
            'title' => 'Contact name',
            'formatter' => fn($data) => $data['contactName']['forename'] . ' ' . $data['contactName']['familyName']
        ],
        [
            'title' => 'Grounds',
            'formatter' => fn($data) => implode(', ', $data['grounds'])
        ],
        [
            'title' => 'Valid',
            'name' => 'isValid'
        ],
        [
            'title' => 'Copied',
            'name' => 'isCopied'
        ],
        [
            'title' => 'In time',
            'name' => 'isInTime'
        ],
        [
            'title' => 'Willing to attend PI',
            'name' => 'isWillingToAttendPi',
        ],
        [
            'title' => 'Withdrawn',
            'name' => 'isWithdrawn'
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

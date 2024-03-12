<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'id' => 'environmental-complaints',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'environmental-complaints']
        ],
        'title' => 'Environmental complaints'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'environmental-complaints',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'environmental-complaints'
    ],
    'columns' => [
        [
            'title' => 'Date received',
            'name' => 'complaintDate'
        ],
        [
            'title' => 'Complainant',
            'formatter' => fn($data, $column) => $data['complainantForename'] . ' ' .
            $data['complainantFamilyName']
        ],
        [
            'title' => 'OC Address',
            'width' => '350px',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Address::class;
                $addressList = '';
                foreach ($data['ocAddress'] as $operatingCentre) {
                    $addressList .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
                }

                return $addressList;
            },
            'name' => 'operatingCentres'
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'append' => '...'
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => empty($data['closeDate']) ?
                $this->translator->translate('Open') : $this->translator->translate('Closed')
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

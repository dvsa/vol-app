<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Environmental complaints',
        'titleSingular' => 'Environmental complaint',
        'action_route' => [
            'route' => 'case_environmental_complaint',
            'params' => []
        ],
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'generate' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Date received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'complaint' => $data['id']],
                    'case_environmental_complaint',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'complaintDate'
        ],
        [
            'title' => 'Complainant',
            'formatter' => fn($data, $column) => $data['complainantContactDetails']['person']['forename'] . ' ' .
            $data['complainantContactDetails']['person']['familyName']
        ],
        [
            'title' => 'OC Address',
            'width' => '350px',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Address::class;
                $addressList = '';
                if (!empty($data['operatingCentres'])) {
                    foreach ($data['operatingCentres'] as $operatingCentre) {
                        $addressList
                            .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
                    }
                }
                return $addressList;
            },
            'name' => 'operatingCentres'
        ],
        [
            'title' => 'Description',
            'name' => 'description'
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => $data['status']['description']
        ]
    ]
];

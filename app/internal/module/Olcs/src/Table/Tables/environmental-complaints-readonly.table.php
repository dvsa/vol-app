<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'title' => 'Environmental complaints',
        'titleSingular' => 'Environmental complaint',
    ],
    'settings' => [],
    'columns' => [
        [
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['case' => $row['case']['id'], 'tab' => 'overview'],
                'case_opposition',
                false
            ) . '">' . $row['case']['id'] . '</a>'
        ],
        [
            'title' => 'Date received',
            'formatter' => Date::class,
            'name' => 'complaintDate'
        ],
        [
            'title' => 'Complainant',
            'formatter' => Name::class,
            'name' => 'complainantContactDetails->person',
        ],
        [
            'title' => 'OC Address',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Address::class;
                $addressList = '';
                foreach ($data['operatingCentres'] as $operatingCentre) {
                    $addressList .= $this->callFormatter($column, $operatingCentre['address']) . '<br/>';
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

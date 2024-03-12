<?php

use Common\Service\Table\Formatter\Date;

$variationNo = 1;
return [
    'variables' => [
        'title' => 'Registration history'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one'],
            ],
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Reg No.',
            'formatter' => fn($data) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['action' => 'index', 'busRegId' => $data['id']],
                'licence/bus-details/service',
                true
            ) . '">' . $data['regNo'] . '</a>',
        ],
        [
            'title' => 'Var No.',
            'isNumeric' => true,
            'name' => 'variationNo'
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data) => $data['status']['description']
        ],
        [
            'title' => 'Application type',
            'formatter' => function ($data, $column) {
                if ($data['isTxcApp'] == 'Y') {
                    if ($data['ebsrRefresh'] == 'Y') {
                        return $this->translator->translate('EBSR Data Refresh');
                    } else {
                        return $this->translator->translate('EBSR');
                    }
                } else {
                    return $this->translator->translate('Manual');
                }
            }
        ],
        [
            'title' => 'Date received',
            'formatter' => Date::class,
            'name' => 'receivedDate'
        ],
        [
            'title' => 'Date effective',
            'formatter' => Date::class,
            'name' => 'effectiveDate'
        ],
        [
            'title' => 'End date',
            'formatter' => Date::class,
            'name' => 'endDate'
        ],
        [
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                if ($data['isLatestVariation']) {
                    return '<input type="radio" name="id" value="' . $data['id'] . '">';
                }
            },
        ],
    ]
];

<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\OrganisationLink;

return [
    'variables' => [
        'title' => 'crud-companies-house-alert-title',
        'titleSingular' => 'crud-companies-house-alert-title-singular',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'close' => [
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => false
                ],
            ]
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
            'title' => 'Company No.',
            'sort' => 'companyOrLlpNo',
            'name' => 'companyOrLlpNo',
        ],
        [
            'title' => 'Licence No.',
            'name' => 'licNo',
            'sort' => 'cha_o_ls.licNo',
            'formatter' => fn($row) => $row['licence']['licNo']
        ],
        [
            'title' => 'Licence Type.',
            'name' => 'description',
            'sort' => 'cha_o_lst.id',
            'formatter' => LicenceTypeShort::class
        ],
        [
            'title' => 'OLCS Company name.',
            'name' => 'organisation',
            'sort' => 'cha_o.name',
            'formatter' => OrganisationLink::class,
        ],


        [
            'title' => 'Reason(s)',
            'name' => 'reason',
            'formatter' => function ($row) {
                if (!isset($row['reasons'])) {
                    return '';
                }
                return implode(
                    ', ',
                    array_map(
                        fn($reason) => $reason['reasonType']['description'],
                        $row['reasons']
                    )
                );
            }
        ],
        [
            'title' => 'Detected',
            'name' => 'createdOn',
            'sort' => 'createdOn',
            'formatter' => Date::class,
        ],
        [
            'title' => 'Select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ],
    ]
];

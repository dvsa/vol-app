<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'transport-manager.competences.table.qualification',
        'dataAttributes' => [
            'data-hard-refresh' => 1
        ]
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Type',
            'name' => 'qualificationType',
            'sort' => 'qualificationType',
            'formatter' => function ($row) {
                $url = $this->generateUrl(
                    ['id' => $row['id'], 'action' => 'edit'],
                    'transport-manager/details/competences'
                );
                return '<a href="'
                    . $url
                    . '" class="govuk-link js-modal-ajax">'
                    . $row['qualificationType']['description']
                    . '</a>';
            },
        ],
        [
            'title' => 'Serial No.',
            'name' => 'serialNo',
            'sort' => 'serialNo',
        ],
        [
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => Date::class,
            'sort' => 'issuedDate',
        ],
        [
            'title' => 'Country',
            'name' => 'Country',
            'sort' => 'Country',
            'formatter' => fn($row) => $row['countryCode']['countryDesc'],
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'deleteInputName' => 'action[delete][%d]'
        ],
    ]
];

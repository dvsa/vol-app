<?php

use Common\Service\Table\Formatter\BusRegStatus;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Common\Service\Table\Formatter\EbsrVariationNumber;

return [
    'variables' => [
        'titleSingular' => 'Bus registration',
        'title' => 'Bus registrations'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [25, 50, 100]
            ]
        ]
    ],
    'columns' => [
        [
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Organisation',
            'name' => 'organisationName'
        ],
        [
            'title' => 'Bus registration No.',
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrRegNumberLink::class;
                return $this->callFormatter($column, $data);
            }
        ],
        [
            'title' => 'Status',
            'formatter' => BusRegStatus::class
        ],
        [
            'title' => 'Variation No.',
            'isNumeric' => true,
            'formatter' => EbsrVariationNumber::class
        ],
        [
            'title' => 'Service No.',
            'isNumeric' => true,
            'formatter' => fn($row) => str_replace('(', ' (', $row['serviceNo'])
        ],
        [
            'title' => '1st-registered-cancelled',
            'name' => 'date1stReg',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
        ],
        [
            'title' => 'Starting point',
            'name' => 'startPoint'
        ],
        [
            'title' => 'Finishing point',
            'name' => 'finishPoint'
        ]
    ]
];

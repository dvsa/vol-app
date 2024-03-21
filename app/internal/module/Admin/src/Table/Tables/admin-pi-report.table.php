<?php

use Common\Service\Table\Formatter\CaseLink;
use Common\Service\Table\Formatter\PiHearingStatus;
use Common\Service\Table\Formatter\PiReportName;
use Common\Service\Table\Formatter\PiReportRecord;
use Common\Service\Table\Formatter\VenueAddress;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'Public Inquiry',
        'title' => 'Public Inquiries'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Case Id',
            'isNumeric' => true,
            'formatter' => fn($data) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->callFormatter(
                    [
                        'formatter' => CaseLink::class,
                    ],
                    $data['pi']['case']
                )
        ],
        [
            'title' => 'Record',
            'formatter' => PiReportRecord::class
        ],
        [
            'title' => 'Name',
            'formatter' => PiReportName::class
        ],
        [
            'title' => 'PI Date & Time',
            'formatter' => fn($data) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->callFormatter(
                    [
                    'name' => 'hearingDate',
                    'formatter' => \Common\Service\Table\Formatter\DateTime::class
                    ],
                    $data
                ) .
            $this->callFormatter(
                [
                    'formatter' => PiHearingStatus::class,
                ],
                $data
            )
        ],
        [
            'title' => 'Venue',
            'formatter' => VenueAddress::class
        ],
    ]
];

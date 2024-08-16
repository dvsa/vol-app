<?php

use Common\Service\Table\Formatter\BusRegStatus;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\EbsrDocumentLink;
use Common\Service\Table\Formatter\EbsrDocumentStatus;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Common\Service\Table\Formatter\EbsrVariationNumber;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'selfserve-table-ebsr-submissions-heading'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'ebsrUpload' => [
                    'value' => 'Upload EBSR files'
                ]
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [25, 50, 100]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'selfserve-table-ebsr-submissions-file-name',
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrDocumentLink::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $data);
            }
        ],
        [
            'title' => 'File status',
            'formatter' => EbsrDocumentStatus::class
        ],
        [
            'title' => 'selfserve-table-ebsr-submissions-reg-number',
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrRegNumberLink::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $data);
            }
        ],
        [
            'title' => 'selfserve-table-ebsr-submissions-variation',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrVariationNumber::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $data);
            }
        ],
        [
            'title' => 'Status',
            'formatter' => BusRegStatus::class
        ],
        [
            'title' => 'selfserve-table-ebsr-submissions-service-numbers',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                $string = '';

                if (isset($data['busReg']['serviceNo'])) {
                    $string = $data['busReg']['serviceNo'];
                    if (isset($data['busReg']['otherServices']) && is_array($data['busReg']['otherServices'])) {
                        foreach ($data['busReg']['otherServices'] as $otherService) {
                            $string .= ', ' . $otherService['serviceNo'];
                        }
                    }
                }
                return $string;
            }
        ],
        [
            'title' => 'selfserve-table-ebsr-submissions-uploaded',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $data);
            },
            'name' => 'submittedDate'
        ],
    ]
];

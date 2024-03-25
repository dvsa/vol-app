<?php

use Common\Service\Table\Formatter\BusRegStatus;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Common\Service\Table\Formatter\EbsrVariationNumber;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'selfserve-table-txc-inbox-heading'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'txc-inbox',
            'actions' => [
                'mark-as-read' => [
                    'value' => 'Mark as read',
                    'requireRows' => true
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
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Organisation',
            'stack' => 'busReg->licence->organisation->name',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'selfserve-table-txc-inbox-reg-number',
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
            'title' => 'Status',
            'formatter' => BusRegStatus::class
        ],
        [
            'title' => 'selfserve-table-txc-inbox-variation',
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
            'title' => 'selfserve-table-txc-inbox-service-numbers',
            'isNumeric' => true,
            'formatter' => function ($data) {
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
            'title' => 'selfserve-table-txc-inbox-uploaded',
            'formatter' => function ($row) {
                // DateTime formatter require data set at root of array
                if (isset($row['busReg']['ebsrSubmissions'][0]['submittedDate'])) {
                    return date(Common\Module::$dateTimeFormat, strtotime($row['busReg']['ebsrSubmissions'][0]['submittedDate']));
                }

                return '';
            }
        ],
        [
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ]
    ]
];

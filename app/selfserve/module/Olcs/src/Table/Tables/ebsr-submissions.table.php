<?php

use Common\Service\Table\Formatter\BusRegStatus;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\EbsrDocumentLink;
use Common\Service\Table\Formatter\EbsrDocumentStatus;
use Common\Service\Table\Formatter\EbsrRegNumberLink;
use Common\Service\Table\Formatter\EbsrVariationNumber;

return array(
    'variables' => array(
        'title' => 'selfserve-table-ebsr-submissions-heading'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'ebsrUpload' => array(
                    'value' => 'Upload EBSR files'
                )
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'selfserve-table-ebsr-submissions-file-name',
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrDocumentLink::class;
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'File status',
            'formatter' => EbsrDocumentStatus::class
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-reg-number',
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrRegNumberLink::class;
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-variation',
            'isNumeric' => true,
            'formatter' => function ($data, $column) {
                $column['formatter'] = EbsrVariationNumber::class;
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => BusRegStatus::class
        ),
        array(
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
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-uploaded',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'submittedDate'
        ),
    )
);

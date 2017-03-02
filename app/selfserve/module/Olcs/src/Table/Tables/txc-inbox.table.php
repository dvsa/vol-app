<?php

return array(
    'variables' => array(
        'title' => 'selfserve-table-txc-inbox-heading'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'txc-inbox',
            'actions' => array(
                'mark-as-read' => array(
                    'value' => 'Mark as read',
                    'requireRows' => true
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
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Organisation',
            'stack' => 'busReg->licence->organisation->name',
            'formatter' => 'StackValue',
        ),
        array(
            'title' => 'selfserve-table-txc-inbox-reg-number',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrRegNumberLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-txc-inbox-variation',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrVariationNumber';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-txc-inbox-service-numbers',
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
        ),
        array(
            'title' => 'selfserve-table-txc-inbox-uploaded',
            'formatter' => function ($row) {
                // DateTime formatter require data set at root of array
                if (isset($row['busReg']['ebsrSubmissions'][0]['submittedDate'])) {
                    return date(\DATETIME_FORMAT, strtotime($row['busReg']['ebsrSubmissions'][0]['submittedDate']));
                }

                return '';
            }
        ),
        array(
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);

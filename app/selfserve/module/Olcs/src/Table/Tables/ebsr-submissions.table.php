<?php

return array(
    'variables' => array(
        'title' => 'selfserve-table-ebsr-submissions-heading'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'ebsrUpload' => array(
                    'value' => 'Upload EBSR files',
                    'class' => 'primary'
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
                $column['formatter'] = 'EbsrDocumentLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-reg-number',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrRegNumberLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-variation',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrVariationNumber';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'selfserve-table-ebsr-submissions-service-numbers',
            'formatter' => function ($data, $column, $sm) {
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
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'submittedDate'
        ),
    )
);

<?php

return array(
    'variables' => array(
        'title' => 'EBSR uploads'
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
            'title' => 'File name',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrDocumentLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'Reg. number',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrRegNumberLink';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'Variation',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'EbsrVariationNumber';
                return $this->callFormatter($column, $data);
            }
        ),
        array(
            'title' => 'Service numbers',
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
            'title' => 'Uploaded',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'submittedDate'
        ),
    )
);

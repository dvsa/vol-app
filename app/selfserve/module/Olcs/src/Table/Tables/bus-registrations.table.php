<?php

$variationNo = 1;
return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Registration No.',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'details', 'busRegId' => $data['busReg']['id']),
                    'bus-registration',
                    false
                ) . '">' . $data['busReg']['regNo'] . '</a>';
            },
            'name' => 'registrationNo',
            'sort' => 'regNo'
        ),
        array(
            'title' => 'Var No.',
            'formatter' => function ($data) {
                return $data['busReg']['variationNo'];
            },
            'sort' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
            'formatter' => function ($data, $column, $sm) {
                $string = $data['busReg']['serviceNo'];
                if (isset($data['busReg']['otherServices']) && is_array($data['busReg']['otherServices'])) {
                    foreach ($data['busReg']['otherServices'] as $otherService) {
                        $string .= ', ' . $otherService['serviceNo'];
                    }
                }
                return $string;
            },
            'sort' => 'serviceNo'
        ),
        array(
            'title' => 'Submitted',
            'formatter' => 'DateTime',
            'name' => 'submittedDate',
            'sort' => 'submittedDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => 'RefData',
            'name' => 'ebsrSubmissionType',
            'sort' => 'ebsrSubmissionType'
        )
    )
);

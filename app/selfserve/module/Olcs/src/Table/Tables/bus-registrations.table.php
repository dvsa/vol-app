<?php

return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'permissionRequisites' => ['local-authority-user'],
            'title' => 'Organisation',
            'formatter' => function ($data) {
                return isset($data['busReg']['licence']['organisation']['name']) ?
                    $data['busReg']['licence']['organisation']['name'] : '';
            },
        ),
        array(
            'title' => 'Registration No.',
            'formatter' => function ($data) {
                if (isset($data['busReg']['id'])) {
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'details', 'busRegId' => $data['busReg']['id']),
                        'bus-registration',
                        false
                    ) . '">' . $data['busReg']['regNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'Var No.',
            'formatter' => function ($data) {
                $string = '';
                if (isset($data['busReg']['variationNo'])) {
                    $string = $data['busReg']['variationNo'];
                }
                return $string;
            }
        ),
        array(
            'title' => 'Service No.',
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
            'title' => 'Submitted',
            'formatter' => 'DateTime',
            'name' => 'submittedDate',
            'sort' => 'submittedDate'
        ),
        array(
            'title' => 'Registration type',
            'formatter' => 'RefData',
            'name' => 'ebsrSubmissionType',
            'sort' => 'ebsrSubmissionType'
        ),
        array(
            'title' => 'File status',
            'formatter' => 'RefData',
            'name' => 'ebsrSubmissionStatus',
            'sort' => 'ebsrSubmissionStatus'
        ),
        array(
            'permissionRequisites' => ['local-authority-admin'],
            'title' => 'Organisation',
            'formatter' => function ($data) {
                return isset($data['busReg']['licence']['organisation']['name']) ?
                    $data['busReg']['licence']['organisation']['name'] : '';
            },
        ),
        array(
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Mark as read',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);

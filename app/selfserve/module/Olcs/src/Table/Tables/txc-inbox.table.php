<?php

return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'txc-inbox',
            'actions' => array(
                'mark-as-read' => array(
                    'value' => 'Mark as read',
                    'class' => 'secondary',
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
            'title' => 'Registration No.',
            'formatter' => function ($data) {
                if (isset($data['busReg']['id'])) {
                    return '<a href="' . $this->generateUrl(
                        array('busRegId' => $data['busReg']['id']),
                        'bus-registration/details',
                        false
                    ) . '">' . $data['busReg']['regNo'] . '</a>';
                }
                return '';
            }
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
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
            'title' => 'Submitted',
            'formatter' => function ($row) {
                // DateTime formatter require data set at root of array
                if (isset($row['busReg']['ebsrSubmissions'][0]['submittedDate'])) {
                    return date(\DATETIME_FORMAT, strtotime($row['busReg']['ebsrSubmissions'][0]['submittedDate']));
                }
            }
        ),
        array(
            'title' => 'Registration type',
            'stack' => 'busReg->ebsrSubmissions->0->ebsrSubmissionType->description',
            'formatter' => 'StackValue'
        ),
        array(
            'title' => 'File status',
            'stack' => 'busReg->ebsrSubmissions->0->ebsrSubmissionStatus->description',
            'formatter' => 'StackValue'
        ),
        array(
            'permissionRequisites' => ['local-authority-admin', 'local-authority-user'],
            'title' => 'Mark as read',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        )
    )
);

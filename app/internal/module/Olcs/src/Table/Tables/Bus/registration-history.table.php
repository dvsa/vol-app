<?php

$variationNo = 1;
return array(
    'variables' => array(
        'title' => 'Registration history'
    ),
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'index', 'busRegId' => $data['id']),
                        'licence/bus-details',
                        true
                    ) . '">' . $data['regNo'] . '</a>';
                },
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['status']['description'];
            }
        ),
        array(
            'title' => 'Application type',
            'formatter' => function ($data, $column, $sm) {
                if ($data['isTxcApp'] == 'Y') {
                    if ($data['ebsrRefresh'] == 'Y') {
                        return $sm->get('translator')->translate('EBSR Data Refresh');
                    } else {
                        return $sm->get('translator')->translate('EBSR');
                    }
                } else {
                    return $sm->get('translator')->translate('Manual');
                }
            }
        ),
        array(
            'title' => 'Date received',
            'formatter' => 'Date',
            'name' => 'receivedDate'
        ),
        array(
            'title' => 'Date effective',
            'formatter' => 'Date',
            'name' => 'effectiveDate'
        ),
        array(
            'title' => 'End date',
            'formatter' => 'Date',
            'name' => 'endDate'
        )
    )
);

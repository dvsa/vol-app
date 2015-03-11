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
            'name' => 'regNo'
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
            'name' => 'serviceNos'
        ),
        array(
            'title' => 'Submitted',
            'formatter' => 'Date',
            'name' => 'submitted'
        ),
        array(
            'title' => 'Type',
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
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                return '<input type="radio" name="id" value="' . $data['id'] . '">';
            },
        ),
    )
);

<?php

return array(
    'variables' => array(
        'title' => 'Applications'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),

        array(
            'title' => 'Lic/App number',
            'formatter' => function($row){
                if (!empty($row['licence']['licenceNumber'])){
                    return $row['licence']['licenceNumber'];
                }
                return 'Not issued yet';
            },
        ),
        array(
            'title' => 'App ID',
            'formatter' => function($row){
                return '<a href="' . $this->url->fromRoute('selfserve/licence-type', ['applicationId' => $row['id'], 'step' => 'operator-location']) . '">'.$row['id'].'</a>';
            },
        ),
        array(
            'title' => 'Date Created',
            'name' => 'createdOn',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Date Submitted',
            'name' => 'receivedDate',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Status',
            'formatter' => function($row){
                return $row['status'];
            },
        ),
    ),
);

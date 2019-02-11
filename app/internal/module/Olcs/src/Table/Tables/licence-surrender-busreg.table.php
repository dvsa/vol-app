<?php
return array(
    'variables' => array(
        'title' => ' active bus registrations associated with this licence.'
    ),
    'attributes' => array(
        'name'=>'openbusregs'
    ),
    'settings' =>[
        'showTotal'=>true
    ],
    'columns' => array(
        array(
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                        array('action' => 'index', 'busRegId' => $data['id']),
                        'licence/bus-details/service',
                        true
                    ) . '">' . $data['regNo'] . '</a>';
            },
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo',
        ),
        array(
            'title' => 'Service No.',
            'name' => 'serviceNo',
        ),
        array(
            'title' => '1st registered / cancelled',
            'formatter' => 'Date',
            'name' => 'date1stReg'
        ),
        array(
            'title' => 'Starting point',
            'name' => 'startPoint',
        ),
        array(
            'title' => 'Finishing point',
            'name' => 'finishPoint',
        ),
        array(
            'title' => 'Status',
            'name' => 'busRegStatusDesc'
        ),
    )
);

<?php

return array(
    'variables' => array(
        'title' => 'Bus registrations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Reg No',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'index', 'busRegId' => $data['id']),
                    'licence/bus-details',
                    true
                ) . '">' . $data['regNo'] . '</a>';
            },
            'sort' => 'regNo'
        ),
        array(
            'title' => 'Var No',
            'name' => 'routeNo',
            'sort' => 'routeNo'
        ),
        array(
            'title' => 'Service No',
            'name' => 'serviceNo',
            'sort' => 'serviceNo'
        )
    )
);

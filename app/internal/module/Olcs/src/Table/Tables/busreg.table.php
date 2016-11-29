<?php

return array(
    'variables' => array(
        'title' => 'Bus registrations'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one')
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
            'title' => 'Reg No.',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('action' => 'index', 'busRegId' => $data['id']),
                    'licence/bus-details/service',
                    true
                ) . '">' . $data['regNo'] . '</a>';
            },
            'sort' => 'routeNo',
        ),
        array(
            'title' => 'Var No.',
            'name' => 'variationNo',
            'sort' => 'variationNo'
        ),
        array(
            'title' => 'Service No.',
            'name' => 'serviceNo',
            'sort' => 'serviceNo'
        ),
        array(
            'title' => '1st registered / cancelled',
            'formatter' => function ($data) {
                return date(\DATE_FORMAT, strtotime($data['date1stReg']));
            },
        ),
        array(
            'title' => 'Starting point',
            'name' => 'startPoint',
            'sort' => 'startPoint'
        ),
        array(
            'title' => 'Finishing point',
            'name' => 'finishPoint',
            'sort' => 'finishPoint'
        ),
        array(
            'title' => 'Status',
            'name' => 'busRegStatusDesc'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);

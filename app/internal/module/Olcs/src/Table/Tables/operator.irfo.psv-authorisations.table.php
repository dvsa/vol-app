<?php

return array(
    'variables' => array(
        'title' => 'PSV Authorisations'
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
                'options' => array(10, 25, 50)
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
            'title' => 'Authorisation Id',
            'name' => 'id'
        ),
        array(
            'title' => 'IRFO File Number',
            'name' => 'irfoFileNo'
        ),
        array(
            'title' => 'In force date',
            'formatter' => 'Date',
            'name' => 'inForceDate'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $column) {
                return $data['irfoPsvAuthType']['description'];
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['status']['description'];
            }
        )
    )
);

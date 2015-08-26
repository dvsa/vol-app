<?php

return array(
    'variables' => array(
        'title' => 'IRFO permits'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'requireRows' => false),
                'in-stock' => array(
                    'label' => 'In Stock', 'class' => 'secondary js-require--multiple', 'requireRows' => true
                ),
                'issued' => array('class' => 'secondary js-require--multiple', 'requireRows' => true),
                'void' => array('class' => 'secondary js-require--multiple', 'requireRows' => true),
                'returned' => array('class' => 'secondary js-require--multiple', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Serial number',
            'name' => 'serialNo',
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data) {
                return $data['status']['description'];
            }
        ),
        array(
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ),
    )
);

<?php

return array(
    'variables' => array(
        'title' => 'Safety inspection providers',
        'empty_message' => 'Please tell us about who will carry out the safety inspections on the vehicles and trailers
            you intend to operate under your licence.',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Selector'
        ),
        array(
            'title' => 'Provider\'s name',
            'name' => 'fao'
        ),
        array(
            'title' => 'External?',
            'name' => 'isExternal',
            'formatter' => 'YesNo'
        ),
        array(
            'title' => 'Workshop address',
            'formatter' => 'Address'
        )
    )
);

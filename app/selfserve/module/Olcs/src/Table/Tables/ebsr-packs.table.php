<?php

return array(
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Pack name',
            'name' => 'filename',
        ),
        array(
            'title' => 'Submitted',
            'name' => 'submitted',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);
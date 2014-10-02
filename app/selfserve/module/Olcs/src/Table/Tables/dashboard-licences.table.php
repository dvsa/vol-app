<?php

return array(
    'variables' => array(
        'title' => 'Licences'
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
            'title' => 'Licence number',
            'name' => 'licNo',
            'formatter' => function ($row) {
                return '<a href="' . $this->url->fromRoute(
                    'licence/overview',
                    ['licence' => $row['id']]
                ) . '">'.$row['licNo'].'</a>';
            }
        ),
        array(
            'title' => 'Licence Type',
            'name' => 'type',
            'formatter' => 'Translate'
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);

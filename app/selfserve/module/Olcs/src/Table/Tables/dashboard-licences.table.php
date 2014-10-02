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
            'formatter' => function ($row, $col, $sm) {
                if (!empty($row['licNo'])) {
                    return $row['licNo'];
                }
                return $sm->get('translator')->translate('Not yet allocated');
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

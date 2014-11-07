<?php

return array(
    'variables' => array(
        'title' => 'Requested penalties'
    ),
    'settings' => array(

    ),
    'columns' => array(
        array(
            'title' => 'Penalty type',
            'formatter' => function ($data) {
                return $data['siPenaltyRequestedType']['description'];
            },
        ),
        array(
            'title' => 'Duration',
            'name' => 'duration',
        ),
    )
);

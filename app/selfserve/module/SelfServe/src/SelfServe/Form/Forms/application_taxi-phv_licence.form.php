<?php

$translationPrefix = 'application_taxi-phv_licence';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'table',
                'options' => array(),
                'type' => 'table-required'
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);

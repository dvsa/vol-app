<?php

$translationPrefix = 'application_vehicle-safety_vehicle-history.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title'
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.licence',
            'name' => 'licenceNo'
        ),
        array(
            'title' => $translationPrefix . '.specified',
            'name' => 'specifiedDate',
            'formatter' => 'DateTime'
        ),
        array(
            'title' => $translationPrefix . '.removed',
            'name' => 'removalDate',
            'formatter' => 'DateTime'
        ),
        array(
            'title' => $translationPrefix . '.disc-no',
            'name' => 'discNo'
        )
    )
);

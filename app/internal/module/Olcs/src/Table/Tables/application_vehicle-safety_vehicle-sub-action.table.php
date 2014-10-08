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
            'formatter' => 'Date',
            'name' => 'specifiedDate'
        ),
        array(
            'title' => $translationPrefix . '.removed',
            'formatter' => 'Date',
            'name' => 'deletedDate'
        ),
        array(
            'title' => $translationPrefix . '.disc-no',
            'name' => 'discNo'
        )
    )
);

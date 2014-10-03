<?php

$translationPrefix = 'dashboard-table-licences';

return array(
    'variables' => array(
        'title' => $translationPrefix
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '-licNo',
            'name' => 'licNo',
            'formatter' => function ($row) {
                // @todo Add the real link in here
                return '<a href="#">'.$row['licNo'].'</a>';
            }
        ),
        array(
            'title' => $translationPrefix . '-licType',
            'name' => 'type',
            'formatter' => 'Translate'
        ),
        array(
            'title' => $translationPrefix . '-status',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);

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
                return '<b><a href="' . $this->url->fromRoute(
                    'lva-licence',
                    array('licence' => $row['id'])
                ) . '">'.$row['licNo'].'</a></b>';
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

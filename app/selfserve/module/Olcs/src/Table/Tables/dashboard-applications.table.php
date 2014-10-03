<?php

$translationPrefix = 'dashboard-table-applications';

return array(
    'variables' => array(
        'title' => $translationPrefix
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '-licNo',
            'formatter' => function ($row, $col, $sm) {
                if (!empty($row['licNo'])) {
                    return $row['licNo'];
                }
                return $sm->get('translator')->translate('Not yet allocated');
            }
        ),
        array(
            'title' => $translationPrefix . '-appId',
            'formatter' => function ($row) {
                // @todo add the real link in here
                return '<a href="#">'.$row['id'].'</a>';
            }
        ),
        array(
            'title' => $translationPrefix . '-createdDate',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '-submittedDate',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '-status',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);

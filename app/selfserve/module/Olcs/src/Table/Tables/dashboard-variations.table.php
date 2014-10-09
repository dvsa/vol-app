<?php

$translationPrefix = 'dashboard-table-variations';

return array(
    'variables' => array(
        'title' => $translationPrefix
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '-appId',
            'formatter' => function ($row) {
                return '<a href="' . $this->url->fromRoute(
                    'lva-variation',
                    array('id' => $row['id'])
                ) . '">'.$row['id'].'</a>';
            }
        ),
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
            'title' => $translationPrefix . '-createdDate',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '-submittedDate',
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

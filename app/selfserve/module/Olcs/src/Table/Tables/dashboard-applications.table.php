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
            'title' => $translationPrefix . '-appId',
            'lva' => 'application',
            'formatter' => 'DashboardApplicationLink'
        ),
        array(
            'title' => $translationPrefix . '-licNo',
            'formatter' => function ($row, $col, $sm) {
                if (!empty($row['licNo'])) {
                    return $row['licNo'];
                }
                return $sm->get('translator')->translate('dashboard-lic-no-not-allocated');
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

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
            'title' => $translationPrefix . '-createdDate',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '-submittedDate',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
    )
);

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
            'lva' => 'variation',
            'formatter' => 'DashboardApplicationLink'
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
    )
);

<?php

$translationPrefix = 'dashboard-table-applications';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'hide_column_headers' => true,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'lva' => 'application',
            'formatter' => 'DashboardApplicationLink'
        ),
    )
);

<?php

$translationPrefix = 'dashboard-table-variations';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'hide_column_headers' => true,
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'lva' => 'variation',
            'formatter' => 'DashboardApplicationLink'
        ),
    )
);

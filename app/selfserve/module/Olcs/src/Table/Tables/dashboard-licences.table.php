<?php

$translationPrefix = 'dashboard-table-licences';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'empty_message' => 'dashboard-no-licences-text',
        'hide_column_headers' => false,
    ),
    'settings' => ['layout' => 'dashboard-licences'],
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => 'dashboard-table-licences-licNo',
            'name' => 'licNo',
            'formatter' => 'LicenceNumberAndStatus'
        ),
        array(
            'title' => 'dashboard-table-licences-status',
            'name' => 'status',
            'formatter' => 'LicenceStatusSelfserve',
        ),
        array(
            'title' => 'dashboard-table-licences-licType',
            'name' => 'type',
            'formatter' => 'Translate'
        ),
        array(
            'title' => 'dashboard-table-licences-area',
            'name' => 'trafficArea',
            'formatter' => 'Translate'
        )
    )
);

<?php

$translationPrefix = 'dashboard-table-licences';

return array(
    'variables' => array(
        'title' => $translationPrefix,
        'empty_message' => 'dashboard-no-licences-text'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'name' => 'licNo',
            'formatter' => 'LicenceNumberAndStatus'
        ),
        array(
            'name' => 'type',
            'formatter' => 'Translate'
        ),
        array(
            'name' => 'trafficArea',
            'formatter' => 'Translate'
        )
    )
);

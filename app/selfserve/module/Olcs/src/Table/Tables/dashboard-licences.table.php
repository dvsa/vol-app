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
            'title' => $translationPrefix . '-licNo',
            'name' => 'licNo',
            'formatter' => 'LicenceNumberAndStatus'
        ),
        array(
            'title' => $translationPrefix . '-licType',
            'name' => 'type',
            'formatter' => 'Translate'
        ),
        array(
            'title' => $translationPrefix . '-area',
            'name' => 'trafficArea',
            'formatter' => 'Translate'
        )
    )
);

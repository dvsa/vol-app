<?php

$translationPrefix = 'entity-view-table-current-applications.table';

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.variationNumber',
            'name' => '',
        ),
        array(
            'title' => $translationPrefix . '.dateReceived',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.datePublished',
            'name' => 'grantedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '.publicationNumber',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.grantDate',
            'name' => 'grantedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.oooDate',
            'name' => 'oooDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.oorDate',
            'name' => 'oooDate',
            'formatter' => 'Date'
        )
    )
);

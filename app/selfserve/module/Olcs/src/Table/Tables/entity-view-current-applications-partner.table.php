<?php

$translationPrefix = 'entity-view-table-current-applications.table';

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(

        array(
            'title' => $translationPrefix . '.variationNumber',
            'name' => 'id'
        ),
        array(
            'title' => $translationPrefix . '.dateReceived',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.datePublished',
            'name' => 'publishedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '.publicationNumber',
            'name' => 'publicationNo'
        ),
        array(
            'title' => $translationPrefix . '.grantDate',
            'name' => 'grantedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '.oooDate',
            'name' => 'oooDate',
            'formatter' => 'Translate',
        ),
        array(
            'title' => $translationPrefix . '.oorDate',
            'name' => 'oorDate',
            'formatter' => 'Translate',
        ),
        array(
            'title' => $translationPrefix . '.objectionRepresentationMade',
            'name' => 'isOpposed',
            'formatter' => 'YesNo',
        )
    )
);

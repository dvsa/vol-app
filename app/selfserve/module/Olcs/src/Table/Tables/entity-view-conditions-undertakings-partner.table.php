<?php

$translationPrefix = 'entity-view-table-conditions-undertakings.table';

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '.description',
            'name' => 'notes'
        ),
        array(
            'title' => $translationPrefix . '.type',
            'formatter' => 'Translate',
            'name' => 'conditionType->description'
        ),
        array(
            'title' => $translationPrefix . '.dateAdded',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '.fulfilled',
            'formatter' => 'YesNo',
            'name' => 'isFulfilled'
        ),
        array(
            'title' => $translationPrefix . '.status',
            'formatter' => function ($data) {
                return $data['isDraft'] == 'Y' ? 'Draft' : 'Approved';
            }
        )
    )
);

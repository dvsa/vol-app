<?php

$translationPrefix = 'entity-view-table-current-applications';

return array(
    'variables' => array(),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => $translationPrefix . '-variation-number',

            'formatter' => 'DashboardApplicationLink'
        ),
        array(
            'title' => $translationPrefix . '-date-received',
            'formatter' => function ($row, $col, $sm) {
                if (!empty($row['licNo'])) {
                    return $row['licNo'];
                }
                return $sm->get('translator')->translate('dashboard-lic-no-not-allocated');
            }
        ),
        array(
            'title' => $translationPrefix . '-date-published',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix  . '-publication-number',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => $translationPrefix . '-grant-date',
            'name' => 'status',
            'formatter' => 'Translate'
        ),
        array(
            'title' => $translationPrefix . '-ooo-date',
            'name' => 'status',
            'formatter' => 'Translate'
        ),
        array(
            'title' => $translationPrefix . '-oor-date',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);

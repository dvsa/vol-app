<?php

return array(
    'variables' => array(
        'title' => 'Applications'
    ),
    'settings' => array(),
    'attributes' => array(),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Lic/App number',
            'formatter' => function ($row, $col, $sm) {
                if (!empty($row['licNo'])) {
                    return $row['licNo'];
                }
                return $sm->get('translator')->translate('Not issued yet');
            }
        ),
        array(
            'title' => 'App ID',
            'formatter' => function ($row) {
                return '<a href="' . $this->url->fromRoute(
                    'Application',
                    ['applicationId' => $row['id']]
                ) . '">'.$row['id'].'</a>';
            }
        ),
        array(
            'title' => 'Date created',
            'name' => 'createdOn',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Date submitted',
            'name' => 'receivedDate',
            'formatter' => 'Date'
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'formatter' => 'Translate'
        )
    )
);

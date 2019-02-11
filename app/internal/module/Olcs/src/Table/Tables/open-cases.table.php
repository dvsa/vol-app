<?php

return array(
    'variables' => array(
        'title' => ' open cases associated with this licence'
    ),
    'attributes' => array(
        'name' => 'opencases'
    ),
    'settings' =>[
        'showTotal'=>true
    ],
    'columns' => array(
        array(
            'title' => 'Case No.',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                        array('case' => $row['id'], 'action' => 'details'),
                        'case',
                        true
                    ) . '">' . $row['id'] . '</a>';
            },
            'sort' => 'id'
        ),
        array(
            'title' => 'Case type',
            'formatter' => function ($row, $column, $sm) {
                if (isset($row['caseType']['description'])) {
                    return $sm->get('translator')->translate($row['caseType']['description']);
                } else {
                    return 'Not set';
                }
            },
            'sort' => 'caseType'
        ),
        array(
            'title' => 'Created',
            'formatter' => 'Date',
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ),

        array(
            'title' => 'Description',
            'formatter' => 'Comment',
            'maxlength' => 250,
            'append' => '...',
            'name' => 'description'
        ),
    )
);

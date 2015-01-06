<?php

return array(
    'variables' => array(
        'title' => 'Cases'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
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
            'title' => 'Closed',
            'formatter' => 'Date',
            'name' => 'closeDate',
            'sort' => 'closeDate'
        ),
        array(
            'title' => 'Description',
            'formatter' => 'comment',
            'maxlength' => 250,
            'append' => '...',
            'name' => 'description'
        ),
        array(
            'title' => 'ECMS',
            'name' => 'ecmsNo'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);

<?php

return array(
    'variables' => array(
        'title' => 'Case list'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
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
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Case Number',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('case' => $row['id'], 'tab' => 'overview'),
                    'case_manage'
                ) . '">' . $row['caseNumber'] . '</a>';
            }
        ),
        array(
            'title' => 'Status',
            'name' => 'status'
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        ),
        array(
            'title' => 'ECMS',
            'name' => 'ecms'
        )
    )
);

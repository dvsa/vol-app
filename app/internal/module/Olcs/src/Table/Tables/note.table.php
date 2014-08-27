<?php

return array(
    'variables' => array(
        'title' => 'Notes'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'value' => 'Create note'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Created',
            'formatter' => function ($data) {
                return '<a href="' . $this->generateUrl(
                    array('case' => $data['id'], 'tab' => 'overview'),
                    'case_manage',
                    true
                ) . '">' . (new \DateTime($data['createdOn']))->format('d/m/Y') . '</a>';
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Author',
            'formatter' => function ($data) {
                    return $data['createdBy']['name'];
                },
            'sort' => 'createdBy'
        ),
        array(
            'title' => 'Note',
            'name' => 'comment',
            'sort' => 'comment'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data) {
                return $data['noteType']['description'];
            },
            'sort' => 'noteType'
        ),
        array(
            'title' => 'Priority',
            'name' => 'priority',
            'sort' => 'priority'
        )
    )
);

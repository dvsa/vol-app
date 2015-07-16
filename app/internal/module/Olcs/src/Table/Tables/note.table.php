<?php

return array(
    'variables' => array(
        'title' => 'Notes',
        'titleSingular' => 'Note',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--multiple'),
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--multiple')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        ),
        'useQuery' => true
    ),
    'columns' => array(
        array(
            'title' => 'Created',
            'formatter' => function ($data) {
                return (new \DateTime($data['createdOn']))->format('d/m/Y');
            },
            'sort' => 'createdOn'
        ),
        array(
            'title' => 'Author',
            'formatter' => function ($data, $column) {

                $column['formatter'] = 'Name';

                return $this->callFormatter($column, $data['user']['contactDetails']['person']);
            }
        ),
        array(
            'title' => 'Note',
            'formatter' => 'Comment',
            'name' => 'comment',
            'sort' => 'comment'
        ),
        array(
            'title' => 'Note type',
            'formatter' => function ($data) {
                return $data['noteType']['description'];
            },
            'sort' => 'noteType'
        ),
        array(
            'title' => 'Priority',
            'name' => 'priority',
            'sort' => 'priority'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);

<?php

return array(
    'variables' => [
        'title' => 'Users'
    ],
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary', 'label' => 'Add user'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one', 'label' => 'Edit'),
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--one', 'label' => 'Remove')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Name',
            'formatter' => function ($row, $column) {

                $column['formatter'] = 'Name';

                return '<a href="#">' . $this->callFormatter($column, $row['contactDetails']['person']) . '</a>';
            }
        ],
        [
            'title' => 'Email',
            'name' => 'emailAddress',
        ],
        [
            'title' => 'Role(s)',
            'formatter' => function ($row, $column) {

                $roles = implode(
                    ', ', array_map(
                        function ($item) {
                            return $item['role']['description'];
                        },
                        $row['userRoles']
                    )
                );

                return $roles;
            }
        ],
    ]
);

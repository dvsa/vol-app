<?php

return array(
    'variables' => array(
        'title' => 'Operators',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'export' => array(
                    'class' => 'action--primary', 
                    'requireRows' => true
                )
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
            'title' => 'ID',
            'name' => 'id',
            'formatter' => function ($row) {
                $column['formatter'] = 'OrganisationLink';
                return $this->callFormatter(
                    $column,
                    [
                        'organisation' => [
                            'id' => $row['id'],
                            'name' => $row['id']
                        ]
                    ]
                );
            }
        ),
        array(
            'title' => 'Operator',
            'name' => 'name',
        ),
        array(
            'title' => 'CPID',
            'name' => 'cpid',
            'formatter' => function ($row) {
                if (is_null($row['cpid'])) {
                    return 'Not Set';
                }

                return $row['cpid']['description'];
            }
        ),
    )
);

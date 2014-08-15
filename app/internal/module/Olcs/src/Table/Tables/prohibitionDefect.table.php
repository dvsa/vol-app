<?php

return array(
    'variables' => array(
        'title' => 'Prohibition defects'
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
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Defect type',
            'formatter' => function ($data, $column) {
                    $column['formatter'] = 'Date';
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'edit', 'id' => $data['id']),
                        'case_prohibition',
                        true
                    ) . '">' . $this->callFormatter($column, $data) . '</a>';
                },
            'name' => 'defectType'
        ),
        array(
            'title' => 'Notes',
            'format' => '{{notes}}'
        )
    )
);
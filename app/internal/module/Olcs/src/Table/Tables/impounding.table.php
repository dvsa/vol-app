<?php

return array(
    'variables' => array(
        'title' => 'Impounding'
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
            'title' => 'Application received',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['id']),
                    'complaint',
                    true
                ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'applicationReceiptDate'
        ),
        array(
            'title' => 'Presiding TC/DTC',
            'format' => '{{presidingTc}}'
        ),
        array(
            'title' => 'Outcome',
            'name' => 'outcome'
        ),
        array(
            'title' => 'Outcome sent',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return $this->callFormatter($column, $data);
            },
            'name' => 'closeDate'

        ),
    )
);

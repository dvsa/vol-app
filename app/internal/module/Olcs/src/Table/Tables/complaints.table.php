<?php

return array(
    'variables' => array(
        'title' => 'Complaints'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
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
            'title' => 'Date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
        return '<a href="' . $this->generateUrl(
                        array('action' => 'edit', 'complaint' => $data['id'])
                ) . '">' . $this->callFormatter($column, $data) . '</a>';
    },
            'name' => 'date'
        ),
        array(
            'title' => 'Complainant name',
            'format' => '{{complainantsForename}} {{complainantsFamilyName}}'
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        )
    )
);

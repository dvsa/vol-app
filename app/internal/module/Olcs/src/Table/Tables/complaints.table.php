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
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant name',
            'formatter' => function ($data, $column) {
                return $data['complainant']['person']['firstName'] . ' ' .
                    $data['complainant']['person']['middleName'] . ' ' .
                    $data['complainant']['person']['surname'];
            },
            //'format' => '{{complainantsForename}} {{complainantsFamilyName}}'
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        )
    )
);

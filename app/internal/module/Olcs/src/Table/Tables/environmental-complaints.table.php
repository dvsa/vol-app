<?php

return array(
    'variables' => array(
        'title' => 'Environmental complaints'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
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
            'title' => 'Date received',
            'formatter' => function ($data, $column) {
                    $column['formatter'] = 'Date';
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'edit', 'complaint' => $data['id']),
                        'case_complaint',
                        true
                    ) . '">' . $this->callFormatter($column, $data) . '</a>';
                },
            'name' => 'complaintDate'
        ),
        array(
            'title' => 'Complainant',
            'formatter' => function ($data, $column) {
                return $data['complainantContactDetails']['forename'] . ' ' .
                $data['complainantContactDetails']['familyName'];
            }
        ),
        array(
            'title' => 'OC Address',
            'name' => 'ocAddress'
        ),
        array(
            'title' => 'Description',
            'name' => 'description'
        ),
        array(
            'title' => 'Status',
            'formatter' => function ($data, $column) {
                return $data['status']['description'];
            }
        )
    )
);

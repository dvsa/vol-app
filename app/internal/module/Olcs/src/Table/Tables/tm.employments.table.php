<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.employments.table'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('label' => 'Add', 'class' => 'primary'),
                'edit' => array('label' => 'Edit', 'class' => 'secondary js-require--one', 'requireRows' => true),
                'delete' => array('label' => 'Remove', 'class' => 'secondary js-require--multiple', 'requireRows' => true)
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Employer',
            'name' => 'employerName',
            'formatter' => function ($row) {
                $routeParams = ['id' => $row['id'], 'action' => 'edit'];
                $url = $this->generateUrl($routeParams);
                return '<a href="' . $url . '" class=js-modal-ajax>' . $row['employerName'] . '</a>';
            },
        ),
        array(
            'title' => 'Address',
            'name' => 'contactDetails->address',
            'formatter' => 'Address'
        ),
        array(
            'title' => 'Position',
            'name' => 'position',
        ),
        array(
            'title' => 'Hours / days',
            'name' => 'hoursPerWeek',
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);

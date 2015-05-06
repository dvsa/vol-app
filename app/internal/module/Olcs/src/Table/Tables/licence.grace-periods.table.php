<?php

return array(
    'variables' => array(
        'title' => 'licence.grace-periods.table.title',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'secondary js-require--multiple')
            ),
            'formName' => 'grace-periods'
        ),
    ),
    'columns' => array(
        array(
            'title' => 'licence.grace-periods.table.startDate',
            'name' => 'startDate',
        ),
        array(
            'title' => 'licence.grace-periods.table.endDate',
            'name' => 'endDate',
        ),
        array(
            'title' => 'licence.grace-periods.table.description',
            'name' => 'description'
        ),
        array(
            'title' => 'licence.grace-periods.table.status',
            'name' => 'status'
        ),
        array(
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);

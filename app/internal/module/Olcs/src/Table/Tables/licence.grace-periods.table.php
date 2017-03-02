<?php

return array(
    'variables' => array(
        'title' => 'licence.grace-periods.table.title',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--multiple')
            ),
            'formName' => 'grace-periods'
        ),
    ),
    'columns' => array(
        array(
            'title' => 'licence.grace-periods.table.startDate',
            'name' => 'startDate',
            'formatter' => function ($data, $column) {
                $column['formatter'] = 'Date';
                return '<a href="' .
                    $this->generateUrl(
                        array(
                            'action' => 'edit',
                            'child_id' => $data['id']
                        ),
                        'licence/grace-periods',
                        true
                    ) .
                    '" class="js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            }
        ),
        array(
            'title' => 'licence.grace-periods.table.endDate',
            'name' => 'endDate',
            'formatter' => 'Date'
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

<?php

return array(
    'variables' => array(
        'title' => 'Submission list'
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
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
        array(
            'title' => 'Submission #',
            'formatter' => function ($row) {
                return '<a href="' . $this->generateUrl(
                    array('case' => $row['id'], 'tab' => 'overview'),
                    'case_manage'
                ) . '">' . $row['id'] . '</a>';
            }
        ),
        array(
            'title' => 'Type',
            'name' => 'type',
        ),
        array(
            'title' => 'Sub status',
            'name' => 'status'
        ),
        array(
            'title' => 'Date created',
            'formatter' => function ($row) {
                return date('d/m/Y', strtotime($row['createdOn']));
            }
        ),
        array(
            'title' => 'Date closed',
            'formatter' => function ($row) {
                return $row['dateClosed']!='' ? date('d/m/Y', strtotime($row['dateClosed'])) : '-';
            }
        ),
        array(
            'title' => 'Currently with',
            'name' => 'currentlyWith'
        ),
        array(
            'title' => 'Urgent',
            'name' => 'urgent'
        )
    )
);

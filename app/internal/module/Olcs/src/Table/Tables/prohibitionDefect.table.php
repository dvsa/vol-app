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
                return '<a href="' . $this->generateUrl(
                    array('action' => 'edit', 'id' => $data['prohibition']['id'], 'defect' => $data['id']),
                    'case_prohibition/defect',
                    true
                ) . '">' . $data['defectType'] . '</a>';
            }
        ),
        array(
            'title' => 'Notes',
            'format' => '{{notes}}'
        )
    )
);

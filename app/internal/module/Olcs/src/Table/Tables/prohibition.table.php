<?php

return array(
    'variables' => array(
        'title' => 'Prohibitions'
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
            'title' => 'Prohibition date',
            'formatter' => function ($data, $column) {
                    $column['formatter'] = 'Date';
                    return '<a href="' . $this->generateUrl(
                        array('action' => 'edit', 'id' => $data['id']),
                        'case_prohibition',
                        true
                    ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'prohibitionDate'
        ),
        array(
            'title' => 'Cleared date',
            'formatter' => 'Date',
            'name' => 'clearedDate',
        ),
        array(
            'title' => 'Vehicle',
            'format' => '{{vrm}}'
        ),
        array(
            'title' => 'Trailer',
            'formatter' => function ($data) {
                switch ($data['isTrailer']) {
                    case 'Y':
                        return 'Yes';
                    case 'N':
                        return 'No';
                    default:
                        return '-';
                }
            }
        ),
        array(
            'title' => 'Imposed At',
            'format' => '{{imposedAt}}'
        ),
        array(
            'title' => 'Type',
            'formatter' => function ($data, $config, $sm) {
                return $sm->get('translator')->translate($data['prohibitionType']['id']);
            }
        )
    )
);

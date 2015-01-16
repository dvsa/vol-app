<?php

return array(
    'variables' => array(
        'title' => 'internal.transport-manager.competences.table.qualification'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('requireRows' => true),
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Type',
            'name' => 'qualificationType',
            'sort' => 'qualificationType',
            'formatter' => function ($row) {
                $url = $this->generateUrl(
                    ['id' => $row['id'], 'action' => 'edit'],
                    'transport-manager/details/competences'
                );
                return '<a href="'
                    . $url
                    . '" class=js-modal-ajax>'
                    . $row['qualificationType']['description']
                    . '</a>';
            },
        ),
        array(
            'title' => 'Serial No.',
            'name' => 'serialNo',
            'sort' => 'serialNo',
        ),
        array(
            'title' => 'Date',
            'name' => 'issuedDate',
            'formatter' => 'Date',
            'sort' => 'issuedDate',
        ),
        array(
            'title' => 'Country',
            'name' => 'Country',
            'sort' => 'Country',
            'formatter' => function ($row) {
                return $row['countryCode']['countryDesc'];
            },
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);

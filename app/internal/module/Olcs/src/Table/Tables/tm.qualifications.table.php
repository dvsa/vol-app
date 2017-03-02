<?php

return array(
    'variables' => array(
        'title' => 'transport-manager.competences.table.qualification',
        'dataAttributes' => [
            'data-hard-refresh' => 1
        ]
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary'),
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
            'type' => 'ActionLinks',
            'deleteInputName' => 'action[delete][%d]'
        ),
    )
);

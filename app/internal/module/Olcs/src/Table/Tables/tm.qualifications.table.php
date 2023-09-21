<?php

use Common\Service\Table\Formatter\Date;

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
                'add' => array('class' => 'govuk-button'),
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
                    . '" class="govuk-link js-modal-ajax">'
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
            'formatter' => Date::class,
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
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'deleteInputName' => 'action[delete][%d]'
        ),
    )
);

<?php

use Common\Service\Table\Formatter\SumColumns;
use Common\Service\Table\Formatter\TmApplicationManagerType;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'transport-manager.responsibilities.table.applications'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'Add', 'class' => 'govuk-button'],
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Manager Type',
            'name' => 'tmType',
            'formatter' => TmApplicationManagerType::class
        ],
        [
            'title' => 'Application ID',
            'name' => 'application',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $routeParams = ['application' => $row['application']['id']];
                $route = $row['application']['isVariation'] ?
                    'lva-variation/transport_managers' : 'lva-application/transport_managers';
                $url = $this->generateUrl($routeParams, $route);
                return '<a class="govuk-link" href="' . $url . '">' .
                    $row['application']['licence']['licNo'] . '/' . $row['application']['id'] .
                    '</a>';
            },
        ],
        [
            'title' => 'Operator name',
            'name' => 'operatorName',
            'formatter' => fn($row) => $row['application']['licence']['organisation']['name'],
        ],
        [
            'title' => 'Hours per week',
            'isNumeric' => true,
            'name' => 'hours',
            'formatter' => SumColumns::class,
            'columns' => ['hoursMon', 'hoursTue', 'hoursWed', 'hoursThu', 'hoursFri', 'hoursSat', 'hoursSun']
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'deleteInputName' => 'table[action][delete-tm-application][%d]'
        ],
    ]
];

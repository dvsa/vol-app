<?php

use Common\Service\Table\Formatter\SumColumns;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'transport-manager.responsibilities.table.licences'
    ],
    'columns' => [
        [
            'title' => 'Manager type',
            'name' => 'tmType',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $routeParams = ['id' => $row['id'], 'action' => 'edit-tm-licence'];
                $url = $this->generateUrl($routeParams);
                return '<a class="govuk-link" href="' . $url . '">' .
                ((isset($row['tmType']['description']) && $row['tmType']['description']) ?
                    $row['tmType']['description'] : 'Not set') . '</a>';
            },
        ],
        [
            'title' => 'Licence No',
            'name' => 'licence',
            'formatter' => function ($row) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $routeParams = ['licence' => $row['licence']['id']];
                $url = $this->generateUrl($routeParams, 'lva-licence/transport_managers');
                return '<a class="govuk-link" href="' . $url . '">' . $row['licence']['licNo'] . '</a>';
            },
        ],
        [
            'title' => 'Operator name',
            'name' => 'operatorName',
            'formatter' => fn($row) => $row['licence']['organisation']['name'],
        ],
        [
            'title' => 'Hours per week',
            'isNumeric' => true,
            'name' => 'hours',
            'formatter' => SumColumns::class,
            'columns' => ['hoursMon', 'hoursTue', 'hoursWed', 'hoursThu', 'hoursFri', 'hoursSat', 'hoursSun']
        ],
    ]
];

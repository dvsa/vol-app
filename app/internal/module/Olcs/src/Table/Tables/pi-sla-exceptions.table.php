<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'titleSingular' => 'SLA Exception',
        'title' => 'SLA Exceptions',
        'empty_message' => 'There are currently no exceptions',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'addCasePiSlaException' => [
                    'class' => 'govuk-button',
                    'label' => 'Add Exception',
                ]
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'SLA Description',
            'formatter' => function ($row, $column) {
                return $row['slaException']['slaDescription'] ?? '';
            }
        ],
        [
            'title' => 'Exception Description',
            'formatter' => function ($row, $column) {
                return $row['slaException']['slaExceptionDescription'] ?? '';
            }
        ],
        [
            'title' => 'Added',
            'name' => 'createdOn',
            'formatter' => Date::class,
            'sort' => 'createdOn'
        ],
        [
            'title' => 'By',
            'formatter' => function ($row, $column) {
                /**
                 * @var \Common\Service\Table\TableBuilder $this
                 * @psalm-scope-this \Common\Service\Table\TableBuilder
                 */
                if (isset($row['createdBy']['contactDetails']['person'])) {
                    $person = $row['createdBy']['contactDetails']['person'];
                    return trim(($person['forename'] ?? '') . ' ' . ($person['familyName'] ?? ''));
                }
                if (isset($row['createdBy']['loginId'])) {
                    return trim($row['createdBy']['loginId']);
                }
                return 'Unknown';
            }
        ]
    ]
];

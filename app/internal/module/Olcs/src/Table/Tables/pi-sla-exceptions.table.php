<?php

use Common\Service\Table\Formatter\Date;
use Common\Util\Escape;

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
                $description = $row['slaException']['slaDescription'] ?? '';
                return Escape::html($description);
            }
        ],
        [
            'title' => 'Exception Description',
            'formatter' => function ($row, $column) {
                $exceptionDescription = $row['slaException']['slaExceptionDescription'] ?? '';
                return Escape::html($exceptionDescription);
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
                    return Escape::html(trim(($person['forename'] ?? '') . ' ' . ($person['familyName'] ?? '')));
                }
                if (isset($row['createdBy']['loginId'])) {
                    return Escape::html(trim($row['createdBy']['loginId']));
                }
                return 'Unknown';
            }
        ]
    ]
];

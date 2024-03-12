<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'titleSingular' => 'Opposition',
        'title' => 'Opposition'
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'Case No.',
            'isNumeric' => true,
            'formatter' => fn($row) => '<a class="govuk-link" href="' . $this->generateUrl(
                ['case' => $row['case']['id'], 'tab' => 'overview'],
                'case_opposition',
                false
            ) . '">' . $row['case']['id'] . '</a>'
        ],
        [
            'title' => 'Case status',
            'name' => 'description',
            'formatter' => fn($row) => ($row['case']['closedDate']) ? 'Closed' : 'Open'
        ],
        [
            'title' => 'Date received',
            'name' => 'raisedDate',
            'formatter' => Date::class,
            'sort' => 'raisedDate',
        ],
        [
            'title' => 'Opposition type',
            'formatter' => RefData::class,
            'name' => 'oppositionType'
        ],
        [
            'title' => 'Name',
            'formatter' => Name::class,
            'name' => 'opposer->contactDetails->person',
        ],
        [
            'title' => 'Grounds',
            'formatter' => function ($data, $column) {
                $grounds = [];
                foreach ($data['grounds'] as $ground) {
                    $grounds[] = $ground['description'];
                }

                return implode(', ', $grounds);
            }
        ],
        [
            'title' => 'App No.',
            'isNumeric' => true,
            'formatter' => fn($row) => $row['case']['application']['id']
        ],
    ]
];

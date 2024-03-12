<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\YesNo;

return [
    'variables' => [
        'titleSingular' => 'Applied penalty',
        'title' => 'Applied penalties'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}',
            'hideWhenDisabled' => true
        ],
        [
            'title' => 'Penalty ID',
            'isNumeric' => true,
            'formatter' => fn($data) => '<a href="' . $this->generateUrl(
                [
                    'action' => 'edit',
                    'id' => $data['id'],
                    'si' => $data['seriousInfringement']['id']
                ],
                'case_penalty_applied',
                true
            ) . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>',
            'hideWhenDisabled' => true
        ],
        [
            'title' => 'Penalty type',
            'formatter' => fn($data) => $data['siPenaltyType']['id'] . ' - ' . $data['siPenaltyType']['description'],
        ],
        [
            'title' => 'Start date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'startDate'
        ],
        [
            'title' => 'End date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return $this->callFormatter($column, $data);
            },
            'name' => 'endDate'
        ],
        [
            'title' => 'Imposed',
            'formatter' => YesNo::class,
            'name' => 'imposed'
        ]
    ]
];

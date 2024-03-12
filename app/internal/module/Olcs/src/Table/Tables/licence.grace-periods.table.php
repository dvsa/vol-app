<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'licence.grace-periods.table.title',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--multiple']
            ],
            'formName' => 'grace-periods'
        ],
    ],
    'columns' => [
        [
            'title' => 'licence.grace-periods.table.startDate',
            'name' => 'startDate',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' .
                    $this->generateUrl(
                        [
                            'action' => 'edit',
                            'child_id' => $data['id']
                        ],
                        'licence/grace-periods',
                        true
                    ) .
                    '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            }
        ],
        [
            'title' => 'licence.grace-periods.table.endDate',
            'name' => 'endDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'licence.grace-periods.table.description',
            'name' => 'description'
        ],
        [
            'title' => 'licence.grace-periods.table.status',
            'name' => 'status'
        ],
        [
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ]
    ]
];

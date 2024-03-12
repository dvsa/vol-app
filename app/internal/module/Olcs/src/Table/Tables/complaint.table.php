<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Complaints',
        'titleSingular' => 'Complaint',
        'empty_message' => 'There are no complaints'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'label' => 'Add complaint'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Date',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'complaint' => $data['id']],
                    'case_complaint',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'complaintDate'
        ],
        [
            'title' => 'Complainant name',
            'formatter' => fn($data, $column) => $data['complainantContactDetails']['person']['forename'] . ' ' .
            $data['complainantContactDetails']['person']['familyName']
        ],
        [
            'title' => 'Description',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'name' => 'description'
        ]
    ]
];

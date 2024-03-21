<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Prohibitions',
        'titleSingular' => 'Prohibition',
        'empty_message' => 'There are no prohibitions'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'label' => 'Add prohibition'],
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
            'title' => 'Prohibition date',
            'formatter' => function ($data, $column) {
                    /**
                     * @var TableBuilder $this
                     * @psalm-scope-this TableBuilder
                     */
                    $column['formatter'] = Date::class;
                    return '<a class="govuk-link" href="' . $this->generateUrl(
                        ['prohibition' => $data['id']],
                        'case_prohibition_defect',
                        true
                    ) . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'prohibitionDate'
        ],
        [
            'title' => 'Cleared date',
            'formatter' => Date::class,
            'name' => 'clearedDate',
        ],
        [
            'title' => 'Vehicle',
            'format' => '{{vrm}}'
        ],
        [
            'title' => 'Trailer',
            'formatter' => function ($data) {
                switch ($data['isTrailer']) {
                    case 'Y':
                        return 'Yes';
                    case 'N':
                        return 'No';
                    default:
                        return '-';
                }
            }
        ],
        [
            'title' => 'Imposed at',
            'format' => '{{imposedAt}}'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data) => $data['prohibitionType']['description']
        ]
    ]
];

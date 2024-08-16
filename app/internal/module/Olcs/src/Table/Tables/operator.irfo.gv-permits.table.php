<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'GV Permit',
        'title' => 'GV Permits'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
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
            'title' => 'Permit Id',
            'isNumeric' => true,
            'formatter' => fn($data) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a href="' . $this->generateUrl(
                    ['action' => 'details', 'id' => $data['id']],
                    'operator/irfo/gv-permits',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>'
        ],
        [
            'title' => 'In force date',
            'formatter' => Date::class,
            'name' => 'inForceDate'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data, $column) => $data['irfoGvPermitType']['description']
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => $data['irfoPermitStatus']['description']
        ]
    ]
];

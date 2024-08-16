<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;
use Olcs\Module;

return [
    'variables' => [
        'title' => 'Not Pi',
    ],
    'settings' => [
        'crud' => [
            'formName' => 'NonPi',
            'actions' => [
                'add' => ['class' => 'govuk-button'],
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
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Meeting date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $url = $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'case_non_pi',
                    true
                );
                $column['formatter'] = Date::class;
                return '<a class="govuk-link" href="' . $url . '">' . date(Module::$dateTimeSecFormat, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ],
        [
            'title' => 'Meeting venue',
            'formatter' => fn($data) => $data['venue']['name'] ?? $data['venueOther']
        ],
        [
            'title' => 'Witness Count',
            'isNumeric' => true,
            'name' => 'witnessCount'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ]
];

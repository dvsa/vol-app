<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'PSV Authorisation',
        'title' => 'PSV Authorisations'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'reset' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Reset'
                ],
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
            'title' => 'Authorisation Id',
            'isNumeric' => true,
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'operator/irfo/psv-authorisations',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>'
        ],
        [
            'title' => 'IRFO File Number',
            'name' => 'irfoFileNo'
        ],
        [
            'title' => 'In force date',
            'formatter' => Date::class,
            'name' => 'inForceDate'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data, $column) => $data['irfoPsvAuthType']['description']
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => $data['status']['description']
        ]
    ]
];

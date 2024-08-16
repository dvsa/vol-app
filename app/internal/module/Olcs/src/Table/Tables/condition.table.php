<?php

use Common\Service\Table\Formatter\Address;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Conditions & undertakings',
        'empty_message' => 'There are no conditions or undertakings'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'conditions',
            'actions' => [
                'add' => ['class' => 'govuk-button', 'label' => 'Add condition or undertaking'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
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
            'title' => 'No.',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'case_conditions_undertakings',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $data['id'] . '</a>',
            'isNumeric' => true,
            'name' => 'id'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->translator->translate($data['conditionType']['description']),
        ],
        [
            'title' => 'Added via',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->translator->translate($data['addedVia']['description']),
        ],
        [
            'title' => 'Fulfilled',
            'formatter' => fn($data, $column) => $data['isFulfilled'] == 'Y' ? 'Yes' : 'No',
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data, $column) => $data['isDraft'] == 'Y' ? 'Draft' : 'Approved',
        ],
        [
            'title' => 'Attached to',
            'formatter' => fn($data, $column) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $this->translator->translate($data['attachedTo']['description']),
        ],
        [
            'title' => 'OC address',
            'width' => '300px',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                if (isset($data['operatingCentre']['address'])) {
                    $column['formatter'] = Address::class;

                    return $this->callFormatter($column, $data['operatingCentre']['address']);
                }

                return 'N/a';
            }
        ],
    ]
];

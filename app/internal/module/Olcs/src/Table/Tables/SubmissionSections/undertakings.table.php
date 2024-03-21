<?php

use Common\Service\Table\Formatter\AddressLines;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'id' => 'undertakings',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'conditions-and-undertakings', 'subSection' => 'undertakings']
        ],
        'title' => 'Undertakings'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'undertakings',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'attributes' => [
        'name' => 'undertakings'
    ],
    'columns' => [
        [
            'title' => 'No.',
            'width' => '8%',
            'name' => 'id'
        ],
        [
            'title' => 'Added via',
            'width' => '8%',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $string = $this->translator->translate($data['addedVia']) . ' '
                    . $data['parentId'];
                return $string;
            }
        ],
        [
            'title' => 'Fulfilled',
            'width' => '8%',
            'formatter' => fn($data, $column) => $data['isFulfilled'] == 'Y' ? 'Yes' : 'No',
        ],
        [
            'title' => 'Status',
            'width' => '8%',
            'formatter' => fn($data, $column) => $data['isDraft'] == 'Y' ? 'Draft' : 'Approved',
        ],
        [
            'title' => 'Attached to',
            'width' => '8%',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $attachedTo = $data['attachedTo'] == 'Operating Centre' ? 'OC' : $data['attachedTo'];
                return $this->translator->translate($attachedTo);
            }
        ],
        [
            'title' => 'OC Address',
            'width' => '20%',
            'formatter' => AddressLines::class,
            'name' => 'OcAddress'
        ],
        [
            'title' => 'Notes',
            'width' => '40%',
            'name' => 'notes',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ],
    ]
];

<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValue;

$translationPrefix = 'application_vehicle-safety_vehicle-psv.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'titleSingular' => $translationPrefix . '.title.singular',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'vehicle',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'id' => 'addSmall'
                ],
                'delete' => [
                    'label' => 'action_links.remove',
                    'class' => ' more-actions__item govuk-button govuk-button--secondary',
                    'requireRows' => true
                ],
                'transfer' => [
                    'label' => 'Transfer',
                    'class' => ' more-actions__item js-require--multiple govuk-button govuk-button--secondary',
                    'requireRows' => true,
                    'id' => 'transferSmall'
                ]
            ]
        ],
        'row-disabled-callback' => static fn($row) => $row['removalDate'] !== null,
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50]
            ]
        ],
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
        'collapseAt' => 1
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'stack' => 'vehicle->vrm',
            'formatter' => StackValue::class,
            'action' => 'edit',
            'type' => 'Action',
            'sort' => 'v.vrm',
        ],
        [
            'title' => $translationPrefix . '.make',
            'stack' => 'vehicle->makeModel',
            'formatter' => StackValue::class,
        ],
        [
            'title' => $translationPrefix . '.specified',
            'name' => 'specifiedDate',
            'formatter' => Date::class,
            'sort' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.removed',
            'name' => 'removalDate',
            'formatter' => Date::class,
            'sort' => 'removalDate'
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'ariaDescription' => static fn($row) => $row['vehicle']['vrm'],
            'isRemoveVisible' => static fn($data) => empty($data['removalDate']),
            'deleteInputName' => 'vehicles[action][delete][%d]'
        ],
        [
            'markup-table-th-action', //this is a view partial from olcs-common
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true
        ]
    ]
];

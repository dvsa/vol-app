<?php

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\StackValueReplacer;
use Common\Service\Table\Formatter\VehicleDiscNo;
use Common\Service\Table\Formatter\VehicleRegistrationMark;

$translationPrefix = 'application_vehicle-safety_vehicle.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'titleSingular' => $translationPrefix . '.titleSingular',
        'empty_message' => 'application_vehicle-safety_vehicle.tableEmptyMessage',
        'within_form' => true
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [],
                'delete' => [
                    'label' => 'action_links.remove',
                    'class' => 'more-actions__item govuk-button govuk-button--secondary',
                    'requireRows' => true
                ],
                // @note other actions may be added dynamically,
                // see Common\Controller\Lva\AbstractGoodsVehiclesController
                // for an example
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => AbstractGoodsVehiclesController::DEF_TABLE_ITEMS_COUNT,
                'options' => [10, 25, 50],
            ],
        ],
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
        'collapseAt' => 1, // this will collapse remaining actions into a 'More Actions' dropdown
        'row-disabled-callback' => static fn($row) => $row['removalDate'] !== null
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.vrm',
            'formatter' => VehicleRegistrationMark::class,
            'action' => 'edit',
            'type' => 'Action',
            'sort' => 'v.vrm'
        ],
        [
            'title' => $translationPrefix . '.weight',
            'isNumeric' => true,
            'stringFormat' => '{vehicle->platedWeight} kg',
            'formatter' => StackValueReplacer::class
        ],
        [
            'title' => $translationPrefix . '.specified',
            'formatter' => Date::class,
            'name' => 'specifiedDate',
            'sort' => 'specifiedDate'
        ],
        [
            'title' => $translationPrefix . '.removed',
            'formatter' => Date::class,
            'name' => 'removalDate',
            'sort' => 'removalDate'
        ],
        [
            'title' => $translationPrefix . '.disc-no',
            'isNumeric' => true,
            'name' => 'discNo',
            'formatter' => VehicleDiscNo::class
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => static fn($row) => $row['vehicle']['vrm'],
            'name' => 'actionRemove',
            'type' => 'ActionLinks',
            'isRemoveVisible' => static fn($data) => empty($data['removalDate'])
        ],
        [
            'name' => 'action',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
            'aria-attributes' => [
                'label' => static fn($data, $translator) => sprintf($translator->translate("licence.vehicle.table.checkbox.aria-label"), $data['vehicle']['vrm']),
            ]
        ]
    ]
];

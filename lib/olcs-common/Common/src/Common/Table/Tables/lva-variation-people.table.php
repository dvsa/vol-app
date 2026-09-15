<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\YesNo;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'selfserve-app-subSection-your-business-people-tableHeaderPeople',
        'empty_message' => 'selfserve-app-subSection-your-business-people-other.table.empty-message',
        'required_label' => 'person',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'Add person'],
            ]
        ],
        'row-disabled-callback' => static fn($row) => in_array($row['action'], ['D', 'C'], true)
    ],
    'columns' => [
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnName',
            'type' => 'VariationRecordAction',
            'action' => 'edit',
            'keepForReadOnly' => true,
            'formatter' => Name::class
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnHasOtherNames',
            'name' => 'otherName',
            'formatter' => YesNo::class,
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnDate',
            'name' => 'birthDate',
            'formatter' => Date::class,
        ],
        [
            'title' => 'Disqual',
            'name' => 'disqual',
            'formatter' => DisqualifyUrl::class
        ],
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnPosition',
            'name' => 'position',
        ],
        [
            'title' => 'markup-table-th-remove-restore', //view partial from olcs-common
            'ariaDescription' => function ($row, $column) {
                $column['formatter'] = Name::class;
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                return $this->callFormatter($column, $row['name']);
            },
            'type' => 'DeltaActionLinks',
        ],
    ]
];

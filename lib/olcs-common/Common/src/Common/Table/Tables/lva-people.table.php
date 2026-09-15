<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\DisqualifyUrl;
use Common\Service\Table\Formatter\NameActionAndStatus;
use Common\Service\Table\Formatter\YesNo;

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
                'add' => [
                    'label' => 'Add person'
                ]
            ]
        ],
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
        'collapseAt' => 1
    ],
    'columns' => [
        [
            'title' => 'selfserve-app-subSection-your-business-people-columnName',
            'name' => 'name',
            'formatter' => NameActionAndStatus::class
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
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => static fn($row) => $row['forename'] . ' ' . $row['familyName'],
            'type' => 'ActionLinks',
            'name' => 'actionLinks'
        ],
    ]
];

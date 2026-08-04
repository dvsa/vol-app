<?php

use Common\Service\Table\Formatter\Translate;

return [
    'variables' => [
        'title' => 'lva.contact-details.phone-contact.table.title',
        'empty_message' => 'lva.contact-details.phone-contact.table.emptyMessage',
        'within_form' => true,
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['label' => 'lva.contact-details.phone-contact.table.action.add'],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'lva.contact-details.phone-contact.table.col.type.title',
            'type' => 'Action',
            'action' => 'edit',
            'name' => 'phoneContactType->description',
            'width' => '40%',
            'formatter' => Translate::class,
            'keepForReadOnly' => true,
        ],
        [
            'title' => 'lva.contact-details.phone-contact.table.col.number.title',
            'isNumeric' => true,
            'name' => 'phoneNumber',
            'width' => '50%',
        ],
        [
            'title' => 'markup-table-th-remove', //this is a view partial from olcs-common
            'ariaDescription' => 'phoneNumber',
            'type' => 'ActionLinks',
            'width' => '10%',
        ],
    ],
];

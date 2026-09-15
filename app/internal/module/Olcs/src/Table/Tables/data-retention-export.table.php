<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Data retention export'
    ],
    'settings' => [
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Description',
            'formatter' => fn($row) => sprintf(
                '%s %s [%s] [%s]',
                \Common\Util\Escape::html($row['organisationName']),
                \Common\Util\Escape::html($row['licNo']),
                \Common\Util\Escape::html($row['entityName']),
                \Common\Util\Escape::html($row['entityPk'])
            ),
        ],
        [
            'title' => 'Deleted date',
            'formatter' => Date::class,
            'name' => 'deletedDate',
        ],
    ]
];

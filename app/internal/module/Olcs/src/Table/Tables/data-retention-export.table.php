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
                $row['organisationName'],
                $row['licNo'],
                $row['entityName'],
                $row['entityPk']
            ),
        ],
        [
            'title' => 'Deleted date',
            'formatter' => Date::class,
            'name' => 'deletedDate',
        ],
    ]
];

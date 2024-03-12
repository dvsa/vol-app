<?php

use Common\Service\Table\Formatter\Name;

return [
    'variables' => [
        'empty_message' => 'entity-view-table-transport-managers.table.empty',
    ],
    'settings' => [],
    'attributes' => ['id' => 'transport-managers'],
    'columns' => [
        [
            'title' => 'name',
            'formatter' => Name::class,
            'name' => 'transportManager->homeCd->person'
        ]
    ]
];

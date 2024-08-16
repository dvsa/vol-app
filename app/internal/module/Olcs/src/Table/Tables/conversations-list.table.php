<?php

use Common\Service\Table\Formatter\InternalConversationLink;

return [
    'variables' => [
        'id' => 'conversations-list-table',
        'title' => 'Conversations',
        'empty_message' => 'There are no conversation records to display'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'name' => 'id',
            'formatter' => InternalConversationLink::class
        ],
    ],
];

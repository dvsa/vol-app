<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\ExternalConversationMessage;

return [
    'attributes' => [
        'class' => 'no-row-border-separator'
    ],
    'variables' => [
        'id' => 'messages-list-table',
        'title' => 'Messages',
        'empty_message' => 'There are no message records linked to this conversation to display'
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
            'formatter' => ExternalConversationMessage::class,
        ],
    ],
];

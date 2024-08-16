<?php

declare(strict_types=1);

use Common\Service\Table\Formatter\ExternalConversationLink;
use Common\Service\Table\Formatter\ExternalConversationStatus;

return [
    'variables'  => [
        'title'         => 'dashboard-messages.table.title',
        'titleSingular' => 'dashboard-messages.table.title',
        'empty_message' => 'dashboard-messages.empty-message',
    ],
    'settings'   => [
        'crud'     => [
            'formName' => 'messages',
            'actions'  => [],
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'attributes' => [],
    'columns'    => [
        [
            'title'     => 'Subject',
            'name'      => 'id',
            'formatter' => ExternalConversationLink::class,
        ],
        [
            'title'     => 'Status',
            'name'      => 'status',
            'formatter' => ExternalConversationStatus::class,
        ],
    ],
];

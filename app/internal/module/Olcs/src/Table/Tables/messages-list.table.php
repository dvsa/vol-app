<?php

use Common\Service\Table\Formatter\InternalConversationMessage;

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
        'crud' => [
            'actions' => [
                'end and archive conversation' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--warning',
                    'label' => 'End and Archive Conversation',
                ],
            ],
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'name' => 'id',
            'formatter' => InternalConversationMessage::class,
        ],
    ],
];

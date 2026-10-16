<?php

return [
    'variables' => [
        'titleSingular' => 'Requested penalty',
        'title' => 'Requested penalties'
    ],
    'settings' => [

    ],
    'columns' => [
        [
            'title' => 'Penalty type',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['siPenaltyRequestedType']['id'])
                . ' - ' . \Common\Util\Escape::html($data['siPenaltyRequestedType']['description']),
        ],
        [
            'title' => 'Requested Identifier',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['penaltyRequestedIdentifier']),
        ],
        [
            'title' => 'Duration',
            'name' => 'duration',
        ],
    ]
];

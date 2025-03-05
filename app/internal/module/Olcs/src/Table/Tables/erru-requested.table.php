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
            'formatter' => fn($data) => $data['siPenaltyRequestedType']['id'] . ' - ' . $data['siPenaltyRequestedType']['description'],
        ],
        [
            'title' => 'Requested Identifier',
            'formatter' => fn($data) => $data['penaltyRequestedIdentifier'],
        ],
        [
            'title' => 'Duration',
            'name' => 'duration',
        ],
    ]
];

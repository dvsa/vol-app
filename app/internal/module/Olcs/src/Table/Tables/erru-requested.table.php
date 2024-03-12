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
            'title' => 'Duration',
            'name' => 'duration',
        ],
    ]
];

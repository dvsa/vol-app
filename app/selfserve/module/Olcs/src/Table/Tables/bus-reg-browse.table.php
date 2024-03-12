<?php
return [
    'variables' => [
        'titleSingular' => 'Bus registration',
        'title' => 'Bus registrations'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [25, 50, 100]
            ]
        ],
        'layout' => 'bus-reg-browse',
    ]
];

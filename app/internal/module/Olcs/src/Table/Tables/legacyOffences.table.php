<?php

use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Legacy offences',
        'titleSingular' => 'Legacy offence',
        'empty_message' => 'There are no legacy offences'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'offence',
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Offence date from',
            'formatter' => function ($data, $column) {
                $url = $this->generateUrl(['action' => 'details', 'id' => $data['id']], 'offence', true);
                $class = 'govuk-link js-modal-ajax';

                if ($data['offenceDate'] == null) {
                    return '<a href="' . $url . '" class="' . $class . '">N/A</a>';
                }

                $column['formatter'] = Date::class;
                return '<a href="' . $url . '" class="' . $class . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'offenceDate'
        ],
        [
            'title' => 'Originating authority',
            'name' => 'offenceAuthority'
        ],
        [
            'title' => 'Vehicle',
            'name' => 'vrm'
        ],
        [
            'title' => 'Trailer',
            'name' => 'isTrailer'
        ],
        [
            'title' => 'Offence detail',
            'name' => 'notes',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'maxlength' => 150,
            'append' => '...'
        ]
    ]
];

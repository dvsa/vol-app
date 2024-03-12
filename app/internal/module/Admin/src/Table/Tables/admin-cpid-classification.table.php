<?php

use Common\Service\Table\Formatter\OrganisationLink;

return [
    'variables' => [
        'titleSingular' => 'Operator',
        'title' => 'Operators',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'export' => [
                    'class' => 'govuk-button', 
                    'requireRows' => true
                ]
            ]
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
            'title' => 'ID',
            'name' => 'id',
            'formatter' => function ($row) {
                $column['formatter'] = OrganisationLink::class;
                return $this->callFormatter(
                    $column,
                    [
                        'organisation' => [
                            'id' => $row['id'],
                            'name' => $row['id']
                        ]
                    ]
                );
            }
        ],
        [
            'title' => 'Operator',
            'name' => 'name',
        ],
        [
            'title' => 'CPID',
            'name' => 'cpid',
            'formatter' => function ($row) {
                if (is_null($row['cpid'])) {
                    return 'Not Set';
                }

                return $row['cpid']['description'];
            }
        ],
    ]
];

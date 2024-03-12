<?php

return [
    'variables' => [
        'titleSingular' => 'Printer',
        'title' => 'Printers'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button', 'requireRows' => false],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 25,
                'options' => [10, 25, 50]
            ],
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Printer',
            'name' => 'printerName',
            'sort' => 'printerName',
            'formatter' => function ($row) {
                $routeParams = ['printer' => $row['id'], 'action' => 'edit'];
                $route = 'admin-dashboard/admin-printing/admin-printer-management';
                $url = $this->generateUrl($routeParams, $route);
                return '<a href="'. $url . '" class="govuk-link js-modal-ajax">' . $row['printerName'] .'</a>';
            },
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ],
        [
            'type' => 'Checkbox',
            'width' => 'checkbox',
        ],
    ]
];

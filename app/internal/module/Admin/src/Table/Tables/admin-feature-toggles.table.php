<?php

use Common\Service\Table\Formatter\FeatureToggleEditLink;
use Common\Service\Table\Formatter\RefDataStatus;

return [
    'variables' => [
        'titleSingular' => 'Feature toggle',
        'title' => 'Feature toggles'
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
            'title' => 'Name',
            'name' => 'friendlyName',
            'formatter' => FeatureToggleEditLink::class
        ],
        [
            'title' => 'Handler (or config key)',
            'name' => 'configName',
        ],
        [
            'title' => 'Status',
            'name' => 'status',
            'formatter' => RefDataStatus::class
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

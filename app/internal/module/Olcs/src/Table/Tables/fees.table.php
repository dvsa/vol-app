<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeNoAndStatus;
use Common\Service\Table\Formatter\FeeUrl;
use Common\Service\Table\Formatter\Money;

return [
    'variables' => [
        'title' => 'Fees',
        'titleSingular' => 'Fee'
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'formName' => 'fees',
            'actions' => [
                'new' => ['class' => 'govuk-button govuk-button--secondary', 'value' => 'New', 'requireRows' => false],
                'pay' => ['class' => 'govuk-button js-require--multiple', 'value' => 'Pay', 'requireRows' => true],
            ]
        ],
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Fee No.',
            'sort' => 'id',
            'name' => 'id',
            'formatter' => FeeNoAndStatus::class,
        ],
        [
            'title' => 'Description',
            'formatter' => FeeUrl::class,
            'sort' => 'description',
        ],
        [
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => Date::class,
            'sort' => 'invoicedDate'
        ],
        [
            'title' => 'Latest payment ref.',
            'name' => 'receiptNo',
        ],
        [
            'title' => 'Fee amount',
            'isNumeric' => true,
            'name' => 'amount',
            'sort' => 'grossAmount',
            'formatter' => FeeAmount::class,
        ],
        [
            'title' => 'Outstanding',
            'isNumeric' => true,
            'name' => 'outstanding',
            'formatter' => Money::class,
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'type' => 'Checkbox',
        ],
    ]
];

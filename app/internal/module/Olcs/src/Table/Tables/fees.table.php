<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\FeeAmount;
use Common\Service\Table\Formatter\FeeNoAndStatus;
use Common\Service\Table\Formatter\FeeUrl;
use Common\Service\Table\Formatter\Money;

return array(
    'variables' => array(
        'title' => 'Fees',
        'titleSingular' => 'Fee'
    ),
    'settings' => array(
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        ),
        'crud' => array(
            'formName' => 'fees',
            'actions' => array(
                'new' => array('class' => 'govuk-button govuk-button--secondary', 'value' => 'New', 'requireRows' => false),
                'pay' => array('class' => 'govuk-button js-require--multiple', 'value' => 'Pay', 'requireRows' => true),
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Fee No.',
            'sort' => 'id',
            'name' => 'id',
            'formatter' => FeeNoAndStatus::class,
        ),
        array(
            'title' => 'Description',
            'formatter' => FeeUrl::class,
            'sort' => 'description',
        ),
        array(
            'title' => 'Created',
            'name' => 'invoicedDate',
            'formatter' => Date::class,
            'sort' => 'invoicedDate'
        ),
        array(
            'title' => 'Latest payment ref.',
            'name' => 'receiptNo',
        ),
        array(
            'title' => 'Fee amount',
            'isNumeric' => true,
            'name' => 'amount',
            'sort' => 'grossAmount',
            'formatter' => FeeAmount::class,
        ),
        array(
            'title' => 'Outstanding',
            'isNumeric' => true,
            'name' => 'outstanding',
            'formatter' => Money::class,
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'type' => 'Checkbox',
        ),
    )
);

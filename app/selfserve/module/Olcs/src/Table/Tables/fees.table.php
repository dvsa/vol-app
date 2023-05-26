<?php

return array(
    'variables' => array(
        'title' => 'dashboard-fees.table.title',
        'empty_message' => 'dashboard-fees-empty-message',
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'fees',
            'actions' => array(
                'pay' => array(
                    'class' => 'govuk-button govuk-button--secondary js-require--multiple',
                    'value' => 'Pay',
                    'requireRows' => true
                ),
            )
        ),
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'selfserve-fees-table-fee-description',
            'name' => 'description',
            'formatter' => 'FeeUrlExternal',
        ),
        array(
            'title' => 'selfserve-fees-table-fee-reference',
            'formatter' => function ($row, $col) {
                return $row['licence']['licNo'];
            },
        ),
        array(
            'title' => 'selfserve-fees-table-fee-licence-outstanding',
            'name' => 'outstanding',
            'formatter' => 'FeeAmount',
            'isNumeric' => true,
        ),
        array(
            'title' => 'action',
            'type' => 'Checkbox',
            'width' => 'checkbox',
            'name' => 'checkbox',
            'disabled-callback' => function ($row) {
                return $row['isExpiredForLicence'];
            }
        )
    ),
    'footer' => array(
        'total' => array(
            'type' => 'th',
            'content' => 'dashboard-fees-total',
            'formatter' => 'Translate',
            'colspan' => 2
        ),
        array(
            'type' => 'th',
            'formatter' => 'FeeAmountSum',
            'name' => 'amount',
            'align' => 'govuk-!-text-align-right',
        ),
        'remainingColspan' => array(
            'type' => 'th',
            'colspan' => 1
        ),
    ),
);

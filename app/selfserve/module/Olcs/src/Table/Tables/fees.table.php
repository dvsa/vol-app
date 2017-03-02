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
                    'class' => 'action--secondary js-require--multiple',
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
            'formatter' => function ($row, $col, $sm) {
                return $row['licence']['licNo'];
            },
        ),
        array(
            'title' => 'selfserve-fees-table-fee-licence-outstanding',
            'name' => 'outstanding',
            'formatter' => 'FeeAmount',
            'align' => 'right',
        ),
        array(
            'title' => '',
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
            'align' => 'right',
        ),
        'remainingColspan' => array(
            'type' => 'th',
            'colspan' => 1
        ),
    ),
);

<?php

return array(
    'variables' => array(
        'title' => 'crud-companies-house-alert-title'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'close' => array('class' => 'primary', 'requireRows' => false),
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'Company No.',
            'name' => 'companyOrLlpNo',
        ),
        array(
            'title' => 'OLCS Company name.',
            'name' => 'organisation',
            'formatter' => function ($row) {
                return $row['organisation']['name'];
            }
        ),
        array(
            'title' => 'Reason(s)',
            'name' => 'reason',
            'formatter' => function ($row) {
                if (is_null($row['reasons'])) {
                    return '';
                }
                return implode(
                    ', ',
                    array_map(
                        function($reason) {
                            return $reason['reasonType']['description'];
                        },
                        $row['reasons']
                    )
                );
            }
        ),
        array(
            'title' => 'Detected',
            'name' => 'createdOn',
            'formatter' => 'Date',
        ),
        array(
            'title' => 'Select',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ),
    )
);

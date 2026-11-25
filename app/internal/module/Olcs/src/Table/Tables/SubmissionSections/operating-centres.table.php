<?php

use Common\Service\Table\Formatter\Address;

return [
    'variables' => [
        'id' => 'operating-centres',
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'operating-centres']
        ],
        'title' => 'Operating centres'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'operating-centres',
            'actions' => [
                'refresh-table' => ['label' => 'Refresh table', 'class' => 'govuk-button govuk-button--secondary', 'requireRows' => false],
                'delete-row' => ['label' => 'Delete row', 'class' => 'govuk-button govuk-button--secondary js-require--multiple', 'requireRows' => true]
            ],
            'action_field_name' => 'formAction'
        ],
        'submission_section' => 'display'
    ],
    'columns' => [
        [
            'title' => 'Address',
            'width' => '350px',
            'formatter' => Address::class,
            'addressFields' => 'FULL',
            'name' => 'OcAddress'
        ],
        [
            'title' => 'Total V-auth',
            'name' => 'totAuthVehicles'
        ],
        [
            'title' => 'Total T-auth',
            'name' => 'totAuthTrailers'
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'label-callback' => static function ($row) {
                $address = $row['OcAddress'] ?? [];
                $parts = [];
                foreach (['addressLine1', 'addressLine2', 'addressLine3', 'addressLine4', 'town', 'postcode'] as $field) {
                    if (!empty($address[$field])) {
                        $parts[] = $address[$field];
                    }
                }
                if (!empty($address['countryCode']['id'])) {
                    $parts[] = $address['countryCode']['id'];
                }
                return 'Select operating centre ' . implode(', ', $parts);
            },
            'hideWhenDisabled' => true
        ],
    ]
];

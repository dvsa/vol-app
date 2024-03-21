<?php

use Common\Service\Table\Formatter\ConstrainedCountriesList;
use Common\Service\Table\Formatter\StackValue;

return [
    'variables' => [
        'title' => 'Permits',
        'titleSingular' => 'Permit',
        'id' => 'candidate-permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'save' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--multiple']
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Permit No.',
            'isNumeric' => true,
            'name' => 'permitNumber',
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => StackValue::class,
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => ConstrainedCountriesList::class
        ],
        [
            'title' => 'Deselect unwanted permits',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                $checked = $data['wanted'] ? 'checked' : '';
                return '<input type="checkbox" name="id[]" value="' . $data['id'] . '" ' . $checked . '>';
            },
        ],
    ],
];

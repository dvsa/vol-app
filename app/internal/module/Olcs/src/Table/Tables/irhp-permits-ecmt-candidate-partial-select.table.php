<?php

return [
    'variables' => [
        'title' => 'Permits',
        'id' => 'candidate-permits',
        'empty_message' => 'There are no permit records to display'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'save' => ['requireRows' => true, 'class' => 'action--secondary js-require--multiple']
            ]
        ],
    ],
    'columns' => [
        [
            'title' => 'Permit No.',
            'name' => 'permitNumber',
        ],
        [
            'title' => 'Minimum emission standard',
            'name' => 'emissionsCategory',
            'stack' => 'irhpPermitRange->emissionsCategory->description',
            'formatter' => 'StackValue',
        ],
        [
            'title' => 'Not valid for travel to',
            'formatter' => 'ConstrainedCountriesList'
        ],

        [
            'title' => 'Deselect unwanted permits',
            'width' => 'checkbox',
            'formatter' => function ($data) {
                $checked = $data['wanted'] ? 'checked' : '';
                return '<input type="checkbox" name="id[]" value="' . $data['id'] . '" '.$checked.'>';
            },
        ],
    ],
];

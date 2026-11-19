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
            'formatter' => function ($data, $column = []) {
                $checked = $data['wanted'] ? 'checked' : '';
                $id = $column['selectionId'] ?? 'candidate-permit-' . $data['id'];
                return '<input type="checkbox" id="' . \Common\Util\Escape::htmlAttr($id)
                    . '" name="id[]" value="' . \Common\Util\Escape::htmlAttr($data['id']) . '" ' . $checked . '>'
                    . '<label class="govuk-visually-hidden" for="' . \Common\Util\Escape::htmlAttr($id)
                    . '">Select permit ' . \Common\Util\Escape::html($data['permitNumber']) . '</label>';
            },
        ],
    ],
];

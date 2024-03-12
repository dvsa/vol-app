<?php

use Common\Service\Table\Formatter\ConvictionDescription;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'Convictions',
        'titleSingular' => 'Conviction',
        'empty_message' => 'There are no convictions'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'conviction',
            'actions' => [
                'add' => ['class' => 'govuk-button', 'label' => 'Add conviction'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'useQuery' => true
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Date of conviction',
            'formatter' => function ($data, $column) {

                $url = $this->generateUrl(['action' => 'edit', 'conviction' => $data['id']], 'conviction', true);
                $class = 'govuk-link js-modal-ajax';
                if ($data['convictionDate'] == null) {
                    return '<a href="' . $url . '" class="' . $class . '">N/A</a>';
                }

                $column['formatter'] = Date::class;
                return '<a href="' . $url . '" class="' . $class . '">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'convictionDate'
        ],
        [
            'title' => 'Date of offence',
            'formatter' => Date::class,
            'name' => 'offenceDate'
        ],
        [
            'title' => 'Name / defendant type',
            'formatter' => function ($data, $column) {

                                $person = $data['personFirstname'] . ' ' . $data['personLastname'];
                $organisationName = $data['operatorName'];
                $name = ($organisationName == '' ? $person : $organisationName) . ' <br /> '
                      . $this->translator->translate($data['defendantType']['description']);

                return $name;
            }
        ],
        [
            'title' => 'Description',
            'formatter' => ConvictionDescription::class,
        ],
        [
            'title' => 'Court/FPN',
            'name' => 'court'
        ],
        [
            'title' => 'Penalty',
            'name' => 'penalty'
        ],
        [
            'title' => 'SI',
            'name' => 'msi'
        ],
        [
            'title' => 'Declared',
            'name' => 'isDeclared'
        ],
        [
            'title' => 'Dealt with',
            'name' => 'isDealtWith'
        ]
    ]
];

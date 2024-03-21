<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;
use Olcs\Module;

return [
    'variables' => [
        'titleSingular' => 'Hearing',
        'title' => 'Hearings'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'conviction',
            'actions' => [
                'addHearing' => ['class' => 'govuk-button', 'value' => 'Add Hearing'],
                'editHearing' => ['requireRows' => true, 'value' => 'Edit Hearing']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => '&nbsp;',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
        [
            'title' => 'Hearing Date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $url = $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'case_pi', true);

                $column['formatter'] = Date::class;
                return '<a class="govuk-link" href="' . $url . '">' . date(Module::$dateFormat, strtotime($data['hearingDate'])) . '</a>';
            },
            'name' => 'id'
        ],
        [
            'title' => 'Is Adjourned',
            'name' => 'isAdjourned'
        ],
        [
            'title' => 'Venue',
            'name' => 'venue'
        ],
        [
            'title' => 'Presiding TC',
            'formatter' => fn($data) => $data['presidingTc']['name']
        ],
    ]
];

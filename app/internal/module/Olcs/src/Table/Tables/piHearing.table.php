<?php

use Olcs\Module;

return [
    'variables' => [
        'titleSingular' => 'Hearing',
        'title' => 'Hearings',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'addHearing' => ['class' => 'govuk-button', 'label' => 'Add'],
                'editHearing' => [
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'requireRows' => true,
                    'label' => 'Edit'
                ],
                'generate' => [
                    'requireRows' => true,
                    'class' => 'govuk-button govuk-button--secondary js-require--one',
                    'label' => 'Generate Letter'
                ],
            ],
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
            'format' => '{{[elements/radio]}}',
            'hideWhenDisabled' => true
        ],
        [
            'title' => 'Date of PI',
            'formatter' => function ($data) {
                $date = date(Module::$dateFormat, strtotime($data['hearingDate']));
                if (!empty($data['pi']['closedDate'])) {
                    return $date;
                } else {
                    $url = $this->generateUrl(
                        ['action' => 'edit', 'id' => $data['id'], 'pi' => $data['pi']['id']],
                        'case_pi_hearing', true
                    );
                    return '<a href="' . $url . '" class="govuk-link js-modal-ajax">' . $date . '</a>';
                }
            },
            'name' => 'id'
        ],
        [
            'title' => 'Venue',
            'formatter' => fn($data) => $data['venue']['name'] ?? $data['venueOther']
        ],
        [
            'title' => 'Adjourned',
            'name' => 'isAdjourned'
        ],
        [
            'title' => 'Cancelled',
            'name' => 'isCancelled'
        ],
        [
            'title' => 'Hearing length',
            'formatter' => function ($data) {
                $hearingLength = 'Not known';
                if ($data['isFullDay'] == 'Y') {
                    $hearingLength = 'Full day';
                } elseif ($data['isFullDay'] == 'N') {
                    $hearingLength = 'Half day';
                }
                return $hearingLength;
            }
        ],
    ]
];

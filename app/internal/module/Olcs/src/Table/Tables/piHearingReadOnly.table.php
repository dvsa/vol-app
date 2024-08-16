<?php

use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'titleSingular' => 'Hearing',
        'title' => 'Hearings',
    ],
    'settings' => [
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
            'title' => 'Date of PI',
            'formatter' => function ($data) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $date = date(Common\Module::$dateFormat, strtotime($data['hearingDate']));
                $url = $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id'], 'pi' => $data['pi']['id']],
                    'case_pi_hearing',
                    true
                );
                return '<a href="' . $url . '" class="govuk-link js-modal-ajax">' . $date . '</a>';
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
    ]
];

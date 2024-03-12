<?php

use Common\Service\Table\Formatter\RefData;

return [
    'variables' => [
        'title' => 'Serious Infringements',
        'titleSingular' => 'Serious Infringement'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one'],
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Id',
            'isNumeric' => true,
            'formatter' => fn($data) => sprintf(
                '<a href="%s" class="govuk-link js-modal-ajax">%s</a>',
                $this->generateUrl(['action' => 'edit', 'id' => $data['id']], 'case_penalty'),
                $data['id']
            )
        ],
        [
            'title' => 'Category',
            'formatter' => RefData::class,
            'name' => 'siCategoryType'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ]
    ]
];

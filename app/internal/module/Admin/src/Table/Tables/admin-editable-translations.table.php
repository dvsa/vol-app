<?php

use Common\Util\Escape;
use Laminas\ServiceManager\ServiceLocatorInterface;

return [
    'variables' => [
        'title' => 'Editable Translations',
        'titleSingular' => 'Editable Translation',
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => [
                    'class' => 'govuk-button',
                    'requireRows' => false
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'govuk-button govuk-button--warning js-require--one'
                ]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Id',
            'isNumeric' => true,
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Content Key',
            'name' => 'translationKey',
            'sort' => 'translationKey',
            'formatter' => fn($row) => Escape::html($row['translationKey']),
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
        ],
        [
            'title' => '',
            'formatter' => function ($data, $column = []) {
                $url = $this->urlHelper->fromRoute(
                    'admin-dashboard/admin-editable-translations',
                    [
                        'action' => 'details',
                        'id' => $data['id']
                    ]
                );

                return sprintf(
                    '<a class="govuk-link" href="%s">%s</a>',
                    $url,
                    $this->translator->translate('view')
                );
            }
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

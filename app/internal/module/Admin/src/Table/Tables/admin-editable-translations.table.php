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
                    'class' => 'action--primary',
                    'requireRows' => false
                ],
                'delete' => [
                    'requireRows' => false,
                    'class' => 'action--secondary js-require--one'
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
            'formatter' => function ($row) {
                return Escape::html($row['translationKey']);
            },
        ],
        [
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description',
        ],
        [
            'title' => '',
            'formatter' => function ($data, $column = array(), ServiceLocatorInterface $sm = null) {
                $url = $sm->get('Helper\Url')->fromRoute(
                    'admin-dashboard/admin-editable-translations',
                    [
                        'action' => 'details',
                        'id' => $data['id']
                    ]
                );

                return sprintf(
                    '<a class="govuk-link" href="%s">%s</a>',
                    $url,
                    $sm->get('translator')->translate('view')
                );
            }
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];

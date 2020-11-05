<?php

use Common\Util\Escape;
use Zend\ServiceManager\ServiceLocatorInterface;

return [
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
            'title' => 'Content Key',
            'name' => 'id',
            'sort' => 'id',
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
                    '<a href="%s">%s</a>',
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

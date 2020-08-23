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
    ],
    'columns' => [
        [
            'title' => 'Id',
            'name' => 'id',
            'sort' => 'id',
        ],
        [
            'title' => 'Partial Key',
            'name' => 'partialKey',
            'sort' => 'partialKey',
            'formatter' => function ($row) {
                return Escape::html($row['partialKey']);
            },
        ],
        [
            'title' => 'prefix',
            'name' => 'prefix',
            'sort' => 'prefix',
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'formatter' => function ($data, $column = array(), ServiceLocatorInterface $sm = null) {
                $url = $sm->get('Helper\Url')->fromRoute(
                    'admin-dashboard/admin-partials',
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
    ]
];

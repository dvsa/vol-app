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
            'title' => 'Content Key',
            'name' => 'id',
        ],
        [
            'title' => 'Description',
            'name' => 'description',
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
    ]
];

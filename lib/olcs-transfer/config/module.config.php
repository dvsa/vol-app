<?php

use Dvsa\Olcs\Transfer\Service;

return [
    'api_router' => [
        'routes' => [
            'api' => [
                'type' => 'Scheme',
                'options' => [
                    'scheme' => 'http'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'backend' => [
                        'type' => \Laminas\Router\Http\Hostname::class,
                        'options' => [
                            'route' => 'olcs-backend'
                        ],
                        'may_terminate' => false,
                        'child_routes' => include('backend-routes.config.php')
                    ]
                ]
            ]
        ]
    ],
    'service_manager' => [
        'factories' => [
            'ApiRouter' => \Dvsa\Olcs\Transfer\Router\RouterFactory::class,
            Service\CacheEncryption::class => Service\CacheEncryptionFactory::class,
            \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder::class => \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilderFactory::class,
        ],
        'invokables' => [
            \Laminas\Xml\Security::class => \Laminas\Xml\Security::class
        ],
        'aliases' => [
            'TransferAnnotationBuilder' => \Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder::class,
        ],
    ],
    'filters' => [
        'invokables' => [
            \Dvsa\Olcs\Transfer\Filter\Vrm::class => \Dvsa\Olcs\Transfer\Filter\Vrm::class
        ],
    ],
    'validators' => [
        'invokables' => [
            \Dvsa\Olcs\Transfer\Validators\Vrm::class => \Dvsa\Olcs\Transfer\Validators\Vrm::class,
            \Dvsa\Olcs\Transfer\Validators\UploadEvidence::class =>
                \Dvsa\Olcs\Transfer\Validators\UploadEvidence::class,
        ],
        'factories' => [
            \Dvsa\Olcs\Transfer\Validators\Xml::class => \Dvsa\Olcs\Transfer\Validators\XmlFactory::class,
        ],
    ],
];

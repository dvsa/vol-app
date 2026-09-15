<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;

return [
    'template' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'template[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'available-templates' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-templates[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Template\AvailableTemplates::class),
                ]
            ],
            'available-template-groups' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'available-template-groups[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Template\AvailableTemplateGroups::class),
                ]
            ],
            'send-test-email' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'send-test-email[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'POST' => CommandConfig::getPostConfig(Command\Template\SendTestEmail::class),
                ]
            ],
            'preview-template-source' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'preview-template-source[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Template\PreviewTemplateSource::class)
                ]
            ],
            'template-source' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'template-source[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Template\TemplateSource::class)
                ]
            ],
            'update-template-source' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'update-template-source[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'PUT' => CommandConfig::getPutConfig(Command\Template\UpdateTemplateSource::class),
                ]
            ],
            'template-categories' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'template-categories[/]'
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\Template\TemplateCategories::class)
                ]
            ],
        ]
    ]
];

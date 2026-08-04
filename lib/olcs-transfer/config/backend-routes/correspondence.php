<?php
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'correspondence' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'correspondence[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Correspondence\Correspondence::class),
                    'access' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => 'access[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\Correspondence\AccessCorrespondence::class
                            ),
                        ]
                    ]
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Correspondence\Correspondences::class),
        ],
    ]
];

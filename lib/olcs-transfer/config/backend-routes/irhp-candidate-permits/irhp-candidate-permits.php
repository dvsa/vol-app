<?php

use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'irhp-candidate-permits' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'irhp-candidate-permits[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'POST' => CommandConfig::getPostConfig(Command\IrhpCandidatePermit\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\IrhpCandidatePermit\ById::class),
                    'PUT' => CommandConfig::getPutConfig(Command\IrhpCandidatePermit\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\IrhpCandidatePermit\Delete::class),
                ]
            ),
            'by-irhp-application' => [
                'type' => 'Segment',
                'options' => [
                    'route' => 'by-irhp-application[/]',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'GET' => QueryConfig::getConfig(Query\IrhpCandidatePermit\GetListByIrhpApplication::class),
                    'unpaged' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => 'unpaged[/]',
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'GET' => QueryConfig::getConfig(Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged::class),
                        ]
                    ],
                ]
            ],
        ]
    ],
];

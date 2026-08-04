<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;
use Dvsa\Olcs\Transfer\Query;

return [
    'transport-manager' => RouteConfig::getRouteConfig(
        'transport-manager',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Tm\TransportManager::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Tm\Update::class),
                    'documents' => RouteConfig::getRouteConfig(
                        'documents',
                        ['GET' => QueryConfig::getConfig(Query\Tm\Documents::class)]
                    ),
                    'merge' => RouteConfig::getRouteConfig(
                        'merge',
                        ['PUT' => CommandConfig::getPutConfig(Command\Tm\Merge::class)]
                    ),
                    'unmerge' => RouteConfig::getRouteConfig(
                        'unmerge',
                        ['PUT' => CommandConfig::getPutConfig(Command\Tm\Unmerge::class)]
                    ),
                    'undo-disqualification' => RouteConfig::getRouteConfig(
                        'undo-disqualification',
                        ['PUT' => CommandConfig::getPutConfig(Command\Tm\UndoDisqualification::class)]
                    )
                ],
                '[0-9]+'
            ),
            'create' => RouteConfig::getRouteConfig(
                'create',
                ['POST' => CommandConfig::getPostConfig(Command\Tm\Create::class),]
            ),
            'create-new-user' => RouteConfig::getRouteConfig(
                'create-new-user',
                ['POST' => CommandConfig::getPostConfig(Command\Tm\CreateNewUser::class),]
            ),
            'remove' => RouteConfig::getRouteConfig(
                'remove',
                ['POST' => CommandConfig::getPostConfig(Command\Tm\Remove::class),]
            ),
            'check-repute' => RouteConfig::getRouteConfig(
                'check-repute',
                ['POST' => CommandConfig::getPostConfig(Command\Tm\CheckRepute::class)]
            ),
        ]
    )
];

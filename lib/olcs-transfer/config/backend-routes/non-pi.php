<?php
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'non-pi' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'non-pi[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET'    => QueryConfig::getConfig(Query\Cases\NonPi\Listing::class),
            'POST'   => CommandConfig::getPostConfig(Command\Cases\NonPi\Create::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT'    => CommandConfig::getPutConfig(Command\Cases\NonPi\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Cases\NonPi\Delete::class),
                ]
            ),
            'named-single' => RouteConfig::getNamedSingleConfig('case',
                [
                    'GET'    => QueryConfig::getConfig(Query\Cases\NonPi\Single::class),
                ]
            )
        ]
    ]
];

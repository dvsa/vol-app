<?php
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'grace-periods' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'grace-periods[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\GracePeriod\GracePeriod::class),
                    'PUT' => CommandConfig::getPutConfig(Command\GracePeriod\UpdateGracePeriod::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\GracePeriod\GracePeriods::class),
            'POST' => CommandConfig::getPostConfig(Command\GracePeriod\CreateGracePeriod::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\GracePeriod\DeleteGracePeriod::class),
        ]
    ]
];
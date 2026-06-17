<?php
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'licence-status-rule' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'licence-status-rule[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'POST' => CommandConfig::getPostConfig(Command\LicenceStatusRule\CreateLicenceStatusRule::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\LicenceStatusRule\LicenceStatusRule::class),
                    'PUT' => CommandConfig::getPutConfig(Command\LicenceStatusRule\UpdateLicenceStatusRule::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\LicenceStatusRule\DeleteLicenceStatusRule::class),
                ]
            ),
        ]
    ]
];
<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'transport-manager-application' => RouteConfig::getRouteConfig(
        'transport-manager-application',
        [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\TransportManagerApplication\GetDetails::class),
                    'update-status' => RouteConfig::getRouteConfig(
                        'update-status',
                        [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerApplication\UpdateStatus::class
                            ),
                        ]
                    ),
                    'update-details' => RouteConfig::getRouteConfig(
                        'update-details',
                        [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerApplication\UpdateDetails::class
                            ),
                        ]
                    ),
                    'send-email' => RouteConfig::getRouteConfig(
                        'send-email',
                        [
                            'POST' => CommandConfig::getPostConfig(
                                Command\TransportManagerApplication\SendTmApplication::class
                            ),
                        ]
                    ),
                    'send-amend-email' => RouteConfig::getRouteConfig(
                        'send-amend-email',
                        [
                            'POST' => CommandConfig::getPostConfig(
                                Command\TransportManagerApplication\SendAmendTmApplication::class
                            ),
                        ]
                    ),
                    'review' => RouteConfig::getRouteConfig(
                        'review',
                        [
                            'GET' => QueryConfig::getConfig(Query\TransportManagerApplication\Review::class)
                        ]
                    ),
                    'submit' => RouteConfig::getRouteConfig(
                        'submit',
                        [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerApplication\Submit::class
                            ),
                        ]
                    ),
                    'operator-signed' => RouteConfig::getRouteConfig(
                        'operator-signed',
                        [
                            'PUT' => CommandConfig::getPutConfig(
                                Command\TransportManagerApplication\OperatorSigned::class
                            ),
                        ]
                    ),
                ],
                '[0-9]+'
            ),
            'POST' => CommandConfig::getPostConfig(Command\TransportManagerApplication\Create::class),
            'DELETE' => CommandConfig::getDeleteConfig(Command\TransportManagerApplication\Delete::class),
            'GET' => QueryConfig::getConfig(Query\TransportManagerApplication\GetList::class),
        ]
    )
];

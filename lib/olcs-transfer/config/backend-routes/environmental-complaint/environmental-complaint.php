<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'environmental-complaint' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'environmental-complaint[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(
                Query\EnvironmentalComplaint\EnvironmentalComplaintList::class
            ),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\EnvironmentalComplaint\EnvironmentalComplaint::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\EnvironmentalComplaint\UpdateEnvironmentalComplaint::class
                    ),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\EnvironmentalComplaint\DeleteEnvironmentalComplaint::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(
                Command\EnvironmentalComplaint\CreateEnvironmentalComplaint::class
            )
        ]
    ],
];

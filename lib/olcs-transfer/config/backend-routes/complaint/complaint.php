<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'complaint' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'complaint[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'GET' => QueryConfig::getConfig(Query\Complaint\ComplaintList::class),
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Complaint\Complaint::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Complaint\UpdateComplaint::class),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Complaint\DeleteComplaint::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Complaint\CreateComplaint::class)
        ]
    ],
];

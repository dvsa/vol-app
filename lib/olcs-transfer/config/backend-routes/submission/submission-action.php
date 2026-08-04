<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'submission-action' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'submission-action[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(
                        Query\Submission\SubmissionAction::class
                    ),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Submission\UpdateSubmissionAction::class
                    ),
                ]
            ),
            'POST' => CommandConfig::getPostConfig(
                Command\Submission\CreateSubmissionAction::class
            )
        ]
    ]
];

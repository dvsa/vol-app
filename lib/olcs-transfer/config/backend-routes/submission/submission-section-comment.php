<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'submission-section-comment' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'submission-section-comment[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Submission\SubmissionSectionComment::class),
                    'PUT' => CommandConfig::getPutConfig(
                        Command\Submission\UpdateSubmissionSectionComment::class
                    ),
                    'DELETE' => CommandConfig::getDeleteConfig(
                        Command\Submission\DeleteSubmissionSectionComment::class
                    )
                ]
            ),
            'POST' => CommandConfig::getPostConfig(Command\Submission\CreateSubmissionSectionComment::class),
        ]
    ]
];

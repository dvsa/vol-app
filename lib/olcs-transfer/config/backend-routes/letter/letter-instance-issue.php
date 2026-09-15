<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-instance-issue' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-instance-issue[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterInstanceIssue\UpdateContent::class),
                ]
            ),
        ]
    ],
];

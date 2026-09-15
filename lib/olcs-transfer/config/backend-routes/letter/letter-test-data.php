<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-test-data' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-test-data[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterTestData\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterTestData\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterTestData\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterTestData\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterTestData\Create::class),
        ]
    ],
];

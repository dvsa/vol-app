<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-appendix' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-appendix[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'GET' => QueryConfig::getConfig(Query\Letter\LetterAppendix\Get::class),
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterAppendix\Update::class),
                    'DELETE' => CommandConfig::getDeleteConfig(Command\Letter\LetterAppendix\Delete::class),
                ]
            ),
            'GET' => QueryConfig::getConfig(Query\Letter\LetterAppendix\GetList::class),
            'POST' => CommandConfig::getPostConfig(Command\Letter\LetterAppendix\Create::class),
        ]
    ],
];

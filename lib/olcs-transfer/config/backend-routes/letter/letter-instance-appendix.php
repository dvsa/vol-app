<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'letter-instance-appendix' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter-instance-appendix[/]',
        ],
        'may_terminate' => false,
        'child_routes' => [
            'single' => RouteConfig::getSingleConfig(
                [
                    'PUT' => CommandConfig::getPutConfig(Command\Letter\LetterInstanceAppendix\UpdateContent::class),
                ]
            ),
        ]
    ],
];

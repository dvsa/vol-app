<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'surrender' => RouteConfig::getRouteConfig(
        'surrender',
        [
            'POST' => CommandConfig::getPostConfig(
                Command\Surrender\Create::class
            ),
            'PUT' => CommandConfig::getPutConfig(
                Command\Surrender\Update::class
            ),
            'DELETE' => CommandConfig::getDeleteConfig(
                Command\Surrender\Delete::class
            ),
            "GET" => QueryConfig::getConfig(
                Dvsa\Olcs\Transfer\Query\Surrender\ByLicence::class
            ),
            'open-cases' => RouteConfig::getRouteConfig(
                'open-cases',
                [
                    'GET' => QueryConfig::getConfig(\Dvsa\Olcs\Transfer\Query\Surrender\OpenCases::class)
                ]
            ),
            'signature' => RouteConfig::getRouteConfig(
                'signature',
                [
                    'GET' => QueryConfig::getConfig(Dvsa\Olcs\Transfer\Query\Surrender\GetSignature::class),
                ]
            ),
            'open-bus-reg' => RouteConfig::getRouteConfig(
                'open-bus-reg',
                [
                    'GET' => QueryConfig::getConfig(Dvsa\Olcs\Transfer\Query\Surrender\OpenBusReg::class),
                ]
            ),
            'submit-form' => RouteConfig::getRouteConfig(
                'submit-form',
                [
                    'POST' => CommandConfig::getPostConfig(Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm::class),
                ]
            ),
            'approve' => RouteConfig::getRouteConfig(
                'approve',
                [
                    'POST' => CommandConfig::getPostConfig(Dvsa\Olcs\Transfer\Command\Surrender\Approve::class),
                ]
            ),
            'withdraw' => RouteConfig::getRouteConfig(
                'withdraw',
                [
                    'POST' => CommandConfig::getPostConfig(Dvsa\Olcs\Transfer\Command\Surrender\Withdraw::class),
                ]
            ),
            'previous-licence-status' => RouteConfig::getRouteConfig(
                'previous-licence-status',
                [
                    'GET' => QueryConfig::getConfig(\Dvsa\Olcs\Transfer\Query\Surrender\PreviousLicenceStatus::class)
                ]
            ),

        ]
    ),
];

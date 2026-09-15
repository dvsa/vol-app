<?php

use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Transfer\Router\CommandConfig;
use Dvsa\Olcs\Transfer\Router\QueryConfig;
use Dvsa\Olcs\Transfer\Router\RouteConfig;

return [
    'contact-details' => RouteConfig::getRouteConfig(
        'contact-details',
        [
            'GET' => QueryConfig::getConfig(Query\ContactDetail\ContactDetailsList::class),
            'phone-contact' => RouteConfig::getRouteConfig(
                'phone-contact',
                [
                    'single' => RouteConfig::getSingleConfig(
                        [
                            'GET' => QueryConfig::getConfig(Query\ContactDetail\PhoneContact\Get::class),
                            'PUT' => CommandConfig::getPutConfig(Command\ContactDetail\PhoneContact\Update::class),
                            'DELETE' => CommandConfig::getDeleteConfig(
                                Command\ContactDetail\PhoneContact\Delete::class
                            ),
                        ]
                    ),
                    'GET' => QueryConfig::getConfig(Query\ContactDetail\PhoneContact\GetList::class),
                    'POST' => CommandConfig::getPostConfig(Command\ContactDetail\PhoneContact\Create::class),
                ]
            ),
        ]
    ),
];

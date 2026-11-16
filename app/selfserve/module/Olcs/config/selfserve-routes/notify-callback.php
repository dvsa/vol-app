<?php

declare(strict_types=1);

use Laminas\Router\Http\Literal;
use Olcs\Controller\NotifyCallbackController;

/**
 * Delivery status callback from GOV.UK Notify. No login, the controller checks a bearer token instead
 * (whitelisted for anonymous access in the RBAC guard, see module.config.php).
 */

return [
    [
        'notify-callback' => [
            'type' => Literal::class,
            'options' => [
                'route' => '/notify/callback',
                'defaults' => [
                    'controller' => NotifyCallbackController::class,
                    'action' => 'index',
                ],
            ],
        ],
    ],
];

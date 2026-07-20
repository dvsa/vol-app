<?php

declare(strict_types=1);

use Laminas\Router\Http\Segment;
use Olcs\Controller\RetrieveController;

/**
 * Anonymous "Retrieve a document" journey.
 *
 * A recipient lands here from an emailed link that carries an opaque :token. No login is
 * required - every route below is whitelisted for anonymous access in the RBAC guard
 * (see module.config.php 'lmc_rbac' => 'guards': 'retrieve' and 'retrieve/*').
 *
 * These routes are merged into the main route stack by the glob loader in module.config.php
 * (config/selfserve-routes/*.php).
 */
return [
    [
        'retrieve' => [
            'type' => Segment::class,
            'options' => [
                'route' => '/retrieve/:token[/]',
                'constraints' => [
                    // Opaque, URL-safe token only - never an internal id.
                    'token' => '[A-Za-z0-9_-]+',
                ],
                'defaults' => [
                    'controller' => RetrieveController::class,
                    'action' => 'index',
                ],
            ],
            'may_terminate' => true,
            'child_routes' => [
                'request-otp' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'request-otp[/]',
                        'defaults' => [
                            'action' => 'requestOtp',
                        ],
                    ],
                ],
                'verify-otp' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'verify-otp[/]',
                        'defaults' => [
                            'action' => 'verifyOtp',
                        ],
                    ],
                ],
                'download' => [
                    'type' => Segment::class,
                    'options' => [
                        'route' => 'download/:memberRef[/]',
                        'constraints' => [
                            // Opaque per-document reference - never an internal id.
                            'memberRef' => '[A-Za-z0-9_-]+',
                        ],
                        'defaults' => [
                            'action' => 'download',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

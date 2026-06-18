<?php

/**
 * Letter parent route configuration
 * Aggregates all letter-related child routes from the letter/ directory
 */

$letterRoutes = [
    'letter' => [
        'type' => 'Segment',
        'options' => [
            'route' => 'letter[/]',
        ],
        'may_terminate' => false,
        'child_routes' => []
    ]
];

// Load all child routes from the letter/ directory
$letterFiles = glob(__DIR__ . '/letter/*.php');
foreach ($letterFiles as $file) {
    $childRoute = include $file;
    $letterRoutes['letter']['child_routes'][key($childRoute)] = current($childRoute);
}

return $letterRoutes;
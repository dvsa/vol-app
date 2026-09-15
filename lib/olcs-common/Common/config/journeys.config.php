<?php

$journeysDirectory = __DIR__ . '/journeys/*.journey.php';

$allRoutes = [];

$journeyArray = array_map(
    static fn($file) => include $file,
    glob($journeysDirectory)
);

$filter = new \Laminas\Filter\Word\CamelCaseToDash();

$controllers = [];

$journeys = [];

foreach ($journeyArray as $journey) {
    foreach ($journey as $name => $details) {
        $journeys[$name] = $details;

        $journeyNamespace = $details['namespace'];

        $controller = $journeyNamespace . '\\' . $name . '\\' . $name . 'Controller';

        $controllers[$controller] = $controller;

        $allRoutes[$name] = [
            'type' => 'segment',
            'options' => [
                'route' => '/' . strtolower($filter->filter($name)) . '[/:' . $details['identifier'] . '][/]',
                'constraints' => [
                    $details['identifier'] => '[0-9]+'
                ],
                'defaults' => [
                    'controller' => $controller,
                    'action' => 'index'
                ]
            ],
            'may_terminate' => true,
            'child_routes' => []
        ];

        foreach ($details['sections'] as $sectionName => $sectionDetails) {
            $namespace = $journeyNamespace . '\\' . $sectionName;

            $controller = $namespace . '\\' . $sectionName . 'Controller';
            $controllers[$controller] = $controller;

            $allRoutes[$name]['child_routes'][$sectionName] = [
                'type' => 'segment',
                'options' => [
                    'route' => strtolower($filter->filter($sectionName)) . '[/]',
                    'defaults' => [
                        'controller' => $controller,
                        'action' => 'index'
                    ]
                ],
                'may_terminate' => true,
                'child_routes' => []
            ];

            foreach ($sectionDetails['subSections'] as $subSectionName => $subSectionDetails) {
                $controller = $namespace . '\\' . $subSectionName . 'Controller';
                $controllers[$controller] = $controller;

                $allRoutes[$name]['child_routes'][$sectionName]['child_routes'][$subSectionName] = [
                    'type' => 'segment',
                    'options' => [
                        'route' => strtolower($filter->filter($subSectionName)) . '[/:action][/:id][/]',
                        'constraints' => [
                            'id' => '[0-9]+'
                        ],
                        'defaults' => [
                            'controller' => $controller,
                            'action' => 'index'
                        ]
                    ]
                ];
            }
        }
    }
}

return [$allRoutes, $controllers, $journeys];

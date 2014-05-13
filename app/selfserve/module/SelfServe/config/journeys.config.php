<?php

$routes = [];

$routeArray = array_map(
    function ($file) {
        return include $file;
    },
    glob(__DIR__ . '/routes/*.routes.php')
);

foreach ($routeArray as $rs) {
    $routes += $rs;
}

$allRoutes = array(
    'selfserve' => array(
        'type' => 'literal',
        'options' => array(
            'route' => '/selfserve'
        ),
        'child_routes' => $routes
    )
);

$journeyArray = array_map(
    function ($file) {
        return include $file;
    },
    glob(__DIR__ . '/journeys/*.journey.php')
);

function camelToHyphen($string) {
    return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $string));
}

$controllers = array();

$journeys = array();

foreach ($journeyArray as $journey) {

    foreach ($journey as $name => $details) {

        $journeys[$name] = $details;

        $journeyNamespace = 'SelfServe\Controller\\' . $name;

        $controller = $journeyNamespace . '\\' . $name . 'Controller';
        $controllers[$controller] = $controller;

        $allRoutes[$name] = array(
            'type' => 'segment',
            'options' => array(
                'route' => '/' . camelToHyphen($name) . '[/:' . $details['identifier'] . '][/]',
                'constraints' => array(
                    $details['identifier'] => '[0-9]+'
                ),
                'defaults' => array(
                    'controller' => $controller,
                    'action' => 'index'
                )
            ),
            'may_terminate' => true,
            'child_routes' => array()
        );

        foreach ($details['sections'] as $sectionName => $sectionDetails) {

            $namespace = $journeyNamespace . '\\' . $sectionName;

            $controller = $namespace . '\\' . $sectionName . 'Controller';
            $controllers[$controller] = $controller;

            $allRoutes[$name]['child_routes'][$sectionName] = array(
                'type' => 'segment',
                'options' => array(
                    'route' => camelToHyphen($sectionName) . '[/]',
                    'defaults' => array(
                        'controller' => $controller,
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array()
            );

            foreach ($sectionDetails['subSections'] as $subSectionName => $subSectionDetails) {

                $controller = $namespace . '\\' . $subSectionName . 'Controller';
                $controllers[$controller] = $controller;

                $allRoutes[$name]['child_routes'][$sectionName]['child_routes'][$subSectionName] = array(
                    'type' => 'segment',
                    'options' => array(
                        'route' => camelToHyphen($subSectionName) . '[/:action][/:id][/]',
                        'constraints' => array(
                            'id' => '[0-9]+'
                        ),
                        'defaults' => array(
                            'controller' => $controller,
                            'action' => 'index'
                        )
                    )
                );
            }
        }
    }
}

return array($allRoutes, $controllers, $journeys);
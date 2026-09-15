<?php

namespace Dvsa\Olcs\Transfer\Router;

/**
 * Route Config
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RouteConfig
{
    public static function getSingleConfig($childRoutes = null, $constraint = null)
    {
        return self::getSingleConfigByName('id', $childRoutes, $constraint);
    }

    public static function getNamedSingleConfig($name, $childRoutes = null, $constraint = null)
    {
        return self::getSingleConfigByName($name, $childRoutes, $constraint);
    }

    public static function getRouteConfig($route, $childRoutes = null)
    {
        $config = [
            'type' => 'Segment',
            'options' => [
                'route' => $route . '[/]'
            ]
        ];

        if ($childRoutes !== null) {
            $config['may_terminate'] = false;
            $config['child_routes'] = $childRoutes;
        }

        return $config;
    }

    private static function getSingleConfigByName($name, $childRoutes = null, $constraint = null)
    {
        $config = [
            'type' => 'Segment',
            'options' => [
                'route' => ':' . $name . '[/]',
                'defaults' => [
                    $name => null
                ]
            ]
        ];

        if (!is_null($constraint)) {
            $config['options']['constraints'][$name] = $constraint;
        }

        if ($childRoutes !== null) {
            $config['may_terminate'] = false;
            $config['child_routes'] = $childRoutes;
        }

        return $config;
    }
}

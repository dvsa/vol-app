<?php

/**
 * Vrm
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Service\Section\VehicleSafety\Vehicle\Formatter;

use Common\Service\Section\VehicleSafety\Vehicle\Formatter\Vrm as ParentFormatter;

/**
 * Vrm
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Vrm extends ParentFormatter
{
    /**
     * Holds the route
     *
     * @var string
     */
    protected static $route = 'licence/details/vehicle';

    /**
     * Return the route for the column
     *
     * @param array $column
     * @return string
     */
    protected static function getRouteForColumn($column)
    {
        return static::$route . (isset($column['psv']) && $column['psv'] ? '_psv' : '');
    }
}

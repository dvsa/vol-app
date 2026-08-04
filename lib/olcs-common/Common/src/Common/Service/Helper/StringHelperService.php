<?php

/**
 * String Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

use Laminas\Filter\Word\DashToCamelCase;
use Laminas\Filter\Word\CamelCaseToDash;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\Filter\Word\CamelCaseToUnderscore;

/**
 * String Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StringHelperService
{
    /**
     * Convert dash to camel case
     *
     * @param string $string
     * @return string
     */
    public function dashToCamel($string)
    {
        $converter = new DashToCamelCase();
        return $converter->filter($string);
    }

    /**
     * Convert camel case to dash
     *
     * @param string $string
     * @return string
     */
    public function camelToDash($string)
    {
        $converter = new CamelCaseToDash();
        return strtolower($converter->filter($string));
    }

    /**
     * Convert camel case to dash
     *
     * @param string $string
     * @return string
     */
    public function camelToUnderscore($string)
    {
        $converter = new CamelCaseToUnderscore();
        return strtolower($converter->filter($string));
    }

    /**
     * Convert underscore to camel case
     *
     * @param string $string
     * @return string
     */
    public function underscoreToCamel($string)
    {
        $converter = new UnderscoreToCamelCase();
        return $converter->filter($string);
    }
}

<?php

/**
 * Value Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Utils\Helper;

/**
 * Value Helper
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ValueHelper
{
    /**
     * @param $value
     * @return bool
     */
    public static function isOn($value)
    {
        return ($value === 'Y' || $value === true || $value == 1);
    }
}

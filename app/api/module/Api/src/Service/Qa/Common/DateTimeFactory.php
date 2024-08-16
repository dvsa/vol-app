<?php

namespace Dvsa\Olcs\Api\Service\Qa\Common;

use DateTime;

class DateTimeFactory
{
    /**
     * Get a DateTime instance representing the specified point in time
     *
     * @param string $time
     *
     * @return DateTime
     */
    public function create($time)
    {
        return new DateTime($time);
    }
}

<?php

namespace Dvsa\Olcs\Api\Service\Qa\Common;

use DateInterval;

class DateIntervalFactory
{
    /**
     * Get a DateInterval instance with the specified interval
     *
     * @param string $intervalSpec
     *
     * @return DateInterval
     */
    public function create($intervalSpec)
    {
        return new DateInterval($intervalSpec);
    }
}

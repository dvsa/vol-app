<?php

namespace Dvsa\Olcs\Api\Service\Common;

use DateTime;

class CurrentDateTimeFactory
{
    /**
     * Get a DateTime instance representing the current point in time
     *
     * @return DateTime
     */
    public function create()
    {
        return new DateTime();
    }
}

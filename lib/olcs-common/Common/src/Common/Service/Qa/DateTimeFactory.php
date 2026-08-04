<?php

namespace Common\Service\Qa;

use DateTime;

class DateTimeFactory
{
    /**
     * Create a DateTime instance representing the current date and time
     *
     * @param string|null $time
     *
     * @return DateTime
     */
    public function create($time = null)
    {
        return new DateTime($time);
    }
}

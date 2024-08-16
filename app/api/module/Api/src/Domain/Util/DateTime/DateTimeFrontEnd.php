<?php

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

/**
 * A DateTime object that has the timezone set to that of the front end
 */
class DateTimeFrontEnd extends DateTime
{
    /**
     * DateTimeFrontEnd constructor.
     *
     * @param string             $time     The Date time
     * @param \DateTimeZone|null $timezone Timezone of the date time
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);
        $this->setTimezone(new \DateTimeZone('Europe/London'));
    }
}

<?php

namespace Common\View\Helper;

use Common\Module;
use Laminas\View\Helper\AbstractHelper;

/**
 * DateTime view helper
 */
class DateTime extends AbstractHelper
{
    /**
     * Get a formatted date and time in Applications timezone
     *
     * @param \DateTime $dateTime   The date time to format
     * @param string    $dateFormat The format required
     *
     * @return string
     */
    public function __invoke(\DateTime $dateTime, $dateFormat = null)
    {
        if (is_null($dateFormat)) {
            $dateFormat = Module::$dateTimeFormat;
        }

        // make a clone of the datetime as we will be altering it and it may be used elsewehere
        $localDateTime = clone $dateTime;
        // change the timezone to whatever the app is running
        $localDateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        return $localDateTime->format($dateFormat);
    }
}

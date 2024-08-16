<?php

namespace Dvsa\Olcs\Api\Domain\Util\DateTime;

/**
 * DateTime
 */
class DateTime extends \DateTime
{
    protected static $now;

    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if ($time == 'now') {
            $now = static::getNow();
            $timezone = $now->getTimezone();
            $time = $now->format(\DateTime::ATOM);
        } elseif ($time === null) {
            $time = (new \DateTime())->format(\DateTime::ATOM);
        }

        parent::__construct($time, $timezone);
    }

    /**
     * @return \DateTime
     */
    protected static function getNow()
    {
        if (is_null(DateTime::$now)) {
            DateTime::$now = new \DateTime();
        }

        return static::$now;
    }
}

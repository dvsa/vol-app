<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

final class SendingWindow
{
    private const TIMEZONE = 'Europe/London';
    private const OPEN_HOUR = 8;
    private const CLOSE_HOUR = 20;

    /**
     * Null when $now is inside 08:00 to 20:00 London time, otherwise the next 08:00 London.
     */
    public static function nextAllowed(\DateTimeImmutable $now): ?\DateTimeImmutable
    {
        $local = $now->setTimezone(new \DateTimeZone(self::TIMEZONE));
        $hour = (int) $local->format('G');

        if ($hour >= self::OPEN_HOUR && $hour < self::CLOSE_HOUR) {
            return null;
        }

        $open = $local->setTime(self::OPEN_HOUR, 0);

        return $hour >= self::CLOSE_HOUR ? $open->modify('+1 day') : $open;
    }
}

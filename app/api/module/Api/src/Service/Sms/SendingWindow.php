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

    /**
     * The "SMS day" a text requested at $now goes out in: 20:00 London to 20:00 London,
     * ending at the first 20:00 at or after the time it will actually be sent.
     *
     * @return array{\DateTimeImmutable, \DateTimeImmutable} [start, end), London time
     */
    public static function smsDay(\DateTimeImmutable $now): array
    {
        $sendAt = (self::nextAllowed($now) ?? $now)->setTimezone(new \DateTimeZone(self::TIMEZONE));

        $end = $sendAt->setTime(self::CLOSE_HOUR, 0);
        if ($end < $sendAt) {
            $end = $end->modify('+1 day');
        }

        return [$end->modify('-1 day'), $end];
    }
}

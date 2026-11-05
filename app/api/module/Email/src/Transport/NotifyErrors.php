<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Alphagov\Notifications\Exception\NotifyException;

final class NotifyErrors
{
    /**
     * 429 (rate limit) and 5xx are transient and must be retried. A code of 0 means the Notify
     * SDK never received an HTTP response at all (connection, DNS, timeout or proxy failure,
     * which php-http reports as code 0). That is the most common transient class, so it must NOT
     * be read as permanent and dropped. Only genuine 4xx client errors (e.g. a malformed
     * recipient) are permanent.
     */
    public static function isTransient(NotifyException $e): bool
    {
        $code = (int) $e->getCode();

        return $code === 429 || $code < 400 || $code >= 500;
    }
}

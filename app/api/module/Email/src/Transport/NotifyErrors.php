<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Alphagov\Notifications\Exception\NotifyException;
use Http\Client\Exception\HttpException;

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

    /**
     * Notify's own error text, e.g. `BadRequestError: "..."`, when the response carried it. Guzzle's
     * message has the URL and a cut-off copy of the body, so that is only the fallback (a gateway
     * page that isn't Notify's JSON, or no response at all).
     */
    public static function message(NotifyException $e): string
    {
        $previous = $e->getPrevious();
        if (!$previous instanceof HttpException) {
            return $e->getMessage();
        }

        $body = json_decode((string) $previous->getResponse()->getBody(), true);
        $parts = [];
        foreach (is_array($body) && is_array($body['errors'] ?? null) ? $body['errors'] : [] as $error) {
            if (is_array($error) && is_string($error['error'] ?? null) && is_string($error['message'] ?? null)) {
                $parts[] = $error['error'] . ': "' . $error['message'] . '"';
            }
        }

        return $parts !== [] ? implode('; ', $parts) : $e->getMessage();
    }
}

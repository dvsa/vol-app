<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

final class MailDsn
{
    /**
     * The configured mail DSN, or null when it is missing, blank or still an unresolved
     * `%placeholder%` (SSM had no value). A DSN that merely contains percent-encoding counts.
     */
    public static function configured(mixed $dsn): ?string
    {
        if (!is_string($dsn)) {
            return null;
        }

        $dsn = trim($dsn);
        if ($dsn === '' || preg_match('/^%[^%]+%$/', $dsn)) {
            return null;
        }

        return $dsn;
    }
}

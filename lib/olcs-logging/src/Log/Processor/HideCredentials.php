<?php

declare(strict_types=1);

namespace Olcs\Logging\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Strip credentials and session identifiers from log records.
 *
 * HidePassword only catches entries whose key or value contains the string "password". A
 * JWT, a session id and a Set-Cookie value contain none of those, so request and response
 * logging passes them through intact: LogRequest logs every request header, which means
 * Authorization: Bearer <JWT> and Cookie: Identity=<session id>, and the outbound client
 * wrapper logs the same on responses.
 *
 * Two independent passes, because neither alone is enough:
 *
 *  - by key, for the headers and payload fields that are credentials by definition
 *  - by value, for anything JWT-shaped, which catches a token logged under a key nobody
 *    thought to list — raw request bodies being the obvious case
 *
 * This is registered unconditionally, unlike HidePassword, which the log.allowPasswordLogging
 * config flag can switch off entirely.
 */
class HideCredentials implements ProcessorInterface
{
    public const REPLACE_WITH = '*** HIDDEN CREDENTIAL ***';

    /**
     * Matched against the key with every non-alphanumeric character removed and the result
     * lowercased, so "refresh_token", "refreshToken" and "Refresh-Token" all collapse to
     * "refreshtoken" and only one spelling needs listing.
     */
    private const SENSITIVE_KEYS = [
        'authorization',
        'proxyauthorization',
        'cookie',
        'setcookie',
        'xsrftoken',
        'accesstoken',
        'refreshtoken',
        'idtoken',
        'jwt',
        'bearer',
        'challengesession',
        'confirmationid',
        'tokenid',
        'sessionid',
        'secret',
        'apikey',
        'privatekey',
    ];

    /**
     * A JWT is three base64url segments separated by dots, and its header all but always
     * begins "eyJ" because every JWT header starts with a "{" then a quoted key. Anchored
     * to a word boundary so it does not fire on arbitrary base64.
     */
    private const JWT_PATTERN = '/\beyJ[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]*/';

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $context = $record->context;
        $extra = $record->extra;

        $this->redact($context);
        $this->redact($extra);

        return $record->with(context: $context, extra: $extra);
    }

    private function redact(array &$data): void
    {
        array_walk_recursive(
            $data,
            function (&$value, $key): void {
                if (is_string($key) && $this->isSensitiveKey($key)) {
                    $value = self::REPLACE_WITH;
                    return;
                }

                if (is_string($value)) {
                    $value = preg_replace(self::JWT_PATTERN, self::REPLACE_WITH, $value);
                }
            }
        );
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalised = strtolower((string) preg_replace('/[^A-Za-z0-9]/', '', $key));

        return in_array($normalised, self::SENSITIVE_KEYS, true);
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Transport;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;

/**
 * Validates attachments against GOV.UK Notify's documented file-size and file-type limits.
 *
 * @see https://docs.notifications.service.gov.uk/php.html#send-a-file-by-email
 */
final class NotifyAttachmentValidator
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    /** @var list<string> */
    public const ALLOWED_EXTENSIONS = [
        'csv', 'doc', 'docx', 'jpeg', 'jpg', 'json', 'odt', 'pdf', 'png', 'rtf', 'txt', 'xlsx',
    ];

    public static function assertAllowed(string $fileName, string $content): void
    {
        $size = strlen($content);
        if ($size > self::MAX_BYTES) {
            throw new EmailNotSentException(sprintf(
                'Notify attachment "%s" is %d bytes, exceeds the %d byte limit',
                $fileName,
                $size,
                self::MAX_BYTES,
            ));
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($extension === '' || !in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new EmailNotSentException(sprintf(
                'Notify attachment "%s" has disallowed extension "%s" (allowed: %s)',
                $fileName,
                $extension,
                implode(', ', self::ALLOWED_EXTENSIONS),
            ));
        }
    }
}

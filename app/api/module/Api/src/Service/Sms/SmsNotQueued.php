<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Sms;

/**
 * A text could not be queued. Nothing was written and the surrounding transaction is still usable,
 * so the caller can carry on without the text.
 */
final class SmsNotQueued extends \RuntimeException
{
    public static function because(\Throwable $previous): self
    {
        return new self('Could not queue SMS: ' . $previous->getMessage(), 0, $previous);
    }
}

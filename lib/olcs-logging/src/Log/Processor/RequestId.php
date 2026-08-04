<?php

namespace Olcs\Logging\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Adds a per-request identifier to the log record's extra data.
 *
 * Mirrors the behaviour of the previous Laminas\Log\Processor\RequestId
 * implementation: derives a stable identifier from $_SERVER['UNIQUE_ID']
 * (mod_unique_id) when present, otherwise from REQUEST_TIME_FLOAT, hashed
 * with sha1 to produce a fixed-width opaque token.
 */
class RequestId implements ProcessorInterface
{
    private ?string $identifier = null;

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['requestId'] = $this->getIdentifier();

        return $record->with(extra: $extra);
    }

    public function getIdentifier(): string
    {
        if ($this->identifier !== null) {
            return $this->identifier;
        }

        $identifier = (string) ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

        if (isset($_SERVER['UNIQUE_ID'])) {
            $identifier = (string) $_SERVER['UNIQUE_ID'];
        }

        $this->identifier = sha1($identifier);

        return $this->identifier;
    }
}

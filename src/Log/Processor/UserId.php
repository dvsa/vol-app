<?php

namespace Olcs\Logging\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class UserId implements ProcessorInterface
{
    private static ?string $userId = null;

    public function setUserId(?string $userId): void
    {
        self::$userId = $userId;
    }

    public function getUserId(): ?string
    {
        return self::$userId;
    }

    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $extra = $record->extra;
        $extra['userId'] = self::$userId;

        return $record->with(extra: $extra);
    }
}

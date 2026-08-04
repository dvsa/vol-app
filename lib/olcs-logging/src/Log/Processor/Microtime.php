<?php

namespace Olcs\Logging\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class Microtime implements ProcessorInterface
{
    #[\Override]
    public function __invoke(LogRecord $record): LogRecord
    {
        $microtime = explode(' ', microtime());
        $extra = $record->extra;
        $extra['microsecs'] = substr($microtime[0], 2, 6);

        return $record->with(extra: $extra);
    }
}

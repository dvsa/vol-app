<?php

namespace Olcs\Logging\Log\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Strip any password-like values from being logged.
 */
class HidePassword implements ProcessorInterface
{
    private const REPLACE_WITH = '*** HIDDEN PASSWORD ***';

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
                if (
                    (is_string($key) && stripos($key, 'password') !== false)
                    || (is_string($value) && stripos($value, 'password') !== false)
                    // CognitoAdapter can throw a trace that doesn't contain the string 'password' but has creds in it.
                    || (is_string($value) && strpos($value, 'CognitoAdapter') !== false)
                ) {
                    $value = self::REPLACE_WITH;
                }
            }
        );
    }
}

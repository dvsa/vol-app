<?php

namespace Olcs\Logging\Test;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * In-memory PSR-3 logger for use in tests.
 *
 * Captures every log call so callers can assert on level, message and
 * context without coupling test code to the concrete logger implementation
 * shipped by this package. If olcs-logging swaps its underlying logger
 * library, only this class needs updating — consumer tests stay put.
 */
final class RecordingLogger extends AbstractLogger
{
    /**
     * @var list<array{level: string, message: string, context: array<mixed>}>
     */
    public array $records = [];

    /**
     * @param mixed $level   Typically a PSR-3 LogLevel string ('debug', 'error', ...)
     *                       but PSR-3 leaves the parameter untyped, so we mirror that.
     * @param string|Stringable $message
     * @param array<mixed> $context
     */
    #[\Override]
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => (string) $level,
            'message' => (string) $message,
            'context' => $context,
        ];
    }

    public function reset(): void
    {
        $this->records = [];
    }

    public function hasRecord(string $level, string $message): bool
    {
        foreach ($this->records as $record) {
            if ($record['level'] === $level && $record['message'] === $message) {
                return true;
            }
        }

        return false;
    }

    public function hasRecordsAtLevel(string $level): bool
    {
        foreach ($this->records as $record) {
            if ($record['level'] === $level) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{level: string, message: string, context: array<mixed>}|null
     */
    public function first(): ?array
    {
        return $this->records[0] ?? null;
    }

    /**
     * @return array{level: string, message: string, context: array<mixed>}|null
     */
    public function last(): ?array
    {
        if ($this->records === []) {
            return null;
        }

        return $this->records[array_key_last($this->records)];
    }
}

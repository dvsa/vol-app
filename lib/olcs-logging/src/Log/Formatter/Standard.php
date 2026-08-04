<?php

namespace Olcs\Logging\Log\Formatter;

use Monolog\Formatter\FormatterInterface;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Emits the OLCS log line as JSON. Format must remain byte-equivalent
 * to the previous laminas-log implementation so CloudWatch dashboards,
 * alerts and downstream parsers continue to work.
 *
 * Example line:
 *   {"timestamp":"2024-01-15 12:34:56.123456","log_priority":7,
 *    "log_priority_name":"DEBUG","log-entry-type":"","openam-uuid":"u1",
 *    "openam_session_token":"s1","correlation_id":"c1","location":"",
 *    "relevant-message":"hello","relevant-data":{...}}
 */
class Standard implements FormatterInterface
{
    /** Laminas-style short level names, indexed by RFC 5424 severity. */
    private const PRIORITY_NAMES = [
        0 => 'EMERG',
        1 => 'ALERT',
        2 => 'CRIT',
        3 => 'ERR',
        4 => 'WARN',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG',
    ];

    #[\Override]
    public function format(LogRecord $record): string
    {
        $context = $record->context;
        $extra = $record->extra;

        $combined = $extra + $context;
        unset($combined['userId'], $combined['sessionId'], $combined['location']);

        $priority = $this->priorityFromLevel($record->level);

        $data = [
            'timestamp' => $this->formatTimestamp($record, $extra),
            'log_priority' => $priority,
            'log_priority_name' => self::PRIORITY_NAMES[$priority],
            'log-entry-type' => $context['type'] ?? '',
            'openam-uuid' => $extra['userId'] ?? null,
            'openam_session_token' => $extra['sessionId'] ?? null,
            'correlation_id' => $extra['correlationId'] ?? ($extra['requestId'] ?? null),
            'location' => $context['location'] ?? '',
            'relevant-message' => $record->message,
            'relevant-data' => $combined,
        ];

        return (string) @json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    #[\Override]
    public function formatBatch(array $records): string
    {
        $out = '';
        foreach ($records as $record) {
            $out .= $this->format($record);
        }
        return $out;
    }

    private function priorityFromLevel(Level $level): int
    {
        return $level->toRFC5424Level();
    }

    private function formatTimestamp(LogRecord $record, array $extra): string
    {
        $datePart = gmdate('Y-m-d H:i:s', $record->datetime->getTimestamp());
        $micro = $extra['microsecs'] ?? substr(explode(' ', microtime())[0], 2, 6);

        return $datePart . '.' . $micro;
    }
}

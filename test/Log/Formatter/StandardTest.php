<?php

namespace OlcsTest\Logging\Log\Formatter;

use DateTimeImmutable;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Formatter\Standard;
use PHPUnit\Framework\TestCase;

class StandardTest extends TestCase
{
    private Standard $sut;

    public function setUp(): void
    {
        $this->sut = new Standard();
    }

    public function testFormatProducesCloudWatchEquivalentJson(): void
    {
        $record = new LogRecord(
            datetime: new DateTimeImmutable('2015-02-18T15:30:22+01:00'),
            channel: 'Logger',
            level: Level::Error,
            message: 'hello world',
            context: [
                'data' => ['foo' => 'bar'],
            ],
            extra: [
                'microsecs' => '145234',
                'userId' => '1',
                'sessionId' => 'adstdjkjht',
                'requestId' => 'REQ_ID',
                'remoteIp' => '192.168.1.54',
                'correlationId' => 'COR_ID',
            ],
        );

        $expected = '{"timestamp":"2015-02-18 14:30:22.145234","log_priority":3,"log_priority_name":"ERR",'
            . '"log-entry-type":"","openam-uuid":"1","openam_session_token":"adstdjkjht","correlation_id":"COR_ID",'
            . '"location":"","relevant-message":"hello world","relevant-data":{"microsecs":"145234",'
            . '"requestId":"REQ_ID","remoteIp":"192.168.1.54","correlationId":"COR_ID","data":{"foo":"bar"}}}';

        $this->assertSame($expected, $this->sut->format($record));
    }

    public function testFormatPullsLogEntryTypeFromContext(): void
    {
        $record = new LogRecord(
            datetime: new DateTimeImmutable('2024-01-15T12:00:00+00:00'),
            channel: 'Logger',
            level: Level::Info,
            message: 'with type',
            context: ['type' => 'Audit', 'location' => 'Module/Foo'],
            extra: ['microsecs' => '000000'],
        );

        $decoded = json_decode($this->sut->format($record), true);

        $this->assertSame('Audit', $decoded['log-entry-type']);
        $this->assertSame('Module/Foo', $decoded['location']);
        // 'location' is consumed; 'type' is not (matches legacy behaviour).
        $this->assertSame(['microsecs' => '000000', 'type' => 'Audit'], $decoded['relevant-data']);
    }

    public function testFormatFallsBackToRequestIdWhenNoCorrelationId(): void
    {
        $record = new LogRecord(
            datetime: new DateTimeImmutable('2024-01-15T12:00:00+00:00'),
            channel: 'Logger',
            level: Level::Debug,
            message: '',
            context: [],
            extra: ['microsecs' => '000000', 'requestId' => 'REQ_ONLY'],
        );

        $decoded = json_decode($this->sut->format($record), true);

        $this->assertSame('REQ_ONLY', $decoded['correlation_id']);
    }

    /**
     * @dataProvider dpPriorityMapping
     */
    public function testPriorityMapping(Level $level, int $expectedPriority, string $expectedName): void
    {
        $record = new LogRecord(
            datetime: new DateTimeImmutable('2024-01-15T12:00:00+00:00'),
            channel: 'Logger',
            level: $level,
            message: '',
            context: [],
            extra: ['microsecs' => '000000'],
        );

        $decoded = json_decode($this->sut->format($record), true);

        $this->assertSame($expectedPriority, $decoded['log_priority']);
        $this->assertSame($expectedName, $decoded['log_priority_name']);
    }

    public static function dpPriorityMapping(): array
    {
        return [
            'emergency' => [Level::Emergency, 0, 'EMERG'],
            'alert' => [Level::Alert, 1, 'ALERT'],
            'critical' => [Level::Critical, 2, 'CRIT'],
            'error' => [Level::Error, 3, 'ERR'],
            'warning' => [Level::Warning, 4, 'WARN'],
            'notice' => [Level::Notice, 5, 'NOTICE'],
            'info' => [Level::Info, 6, 'INFO'],
            'debug' => [Level::Debug, 7, 'DEBUG'],
        ];
    }
}

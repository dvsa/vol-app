<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Sms;

use Dvsa\Olcs\Api\Service\Sms\SendingWindow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SendingWindowTest extends TestCase
{
    #[DataProvider('cases')]
    public function testNextAllowed(string $now, ?string $expected): void
    {
        $tz = new \DateTimeZone('Europe/London');
        $result = SendingWindow::nextAllowed(new \DateTimeImmutable($now, $tz));

        if ($expected === null) {
            $this->assertNull($result);
            return;
        }

        $this->assertSame('Europe/London', $result->getTimezone()->getName());
        $this->assertSame($expected, $result->format('Y-m-d H:i T'));
    }

    public static function cases(): array
    {
        return [
            'before open' => ['2026-11-05 07:59', '2026-11-05 08:00 GMT'],
            'at open' => ['2026-11-05 08:00', null],
            'before close' => ['2026-11-05 19:59', null],
            'at close' => ['2026-11-05 20:00', '2026-11-06 08:00 GMT'],
            'evening' => ['2026-11-05 21:30', '2026-11-06 08:00 GMT'],
            'after midnight' => ['2026-11-06 00:30', '2026-11-06 08:00 GMT'],
            'clocks go forward' => ['2026-03-28 21:30', '2026-03-29 08:00 BST'],
        ];
    }

    public function testUtcInputIsConvertedToLondon(): void
    {
        // 07:30 UTC in July is 08:30 BST, inside the window.
        $this->assertNull(SendingWindow::nextAllowed(new \DateTimeImmutable('2026-07-01 07:30', new \DateTimeZone('UTC'))));
        // 19:30 UTC in July is 20:30 BST, outside.
        $next = SendingWindow::nextAllowed(new \DateTimeImmutable('2026-07-01 19:30', new \DateTimeZone('UTC')));
        $this->assertSame('2026-07-02 08:00 BST', $next->format('Y-m-d H:i T'));
    }
}

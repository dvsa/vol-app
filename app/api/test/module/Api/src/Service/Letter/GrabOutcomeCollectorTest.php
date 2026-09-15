<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Service\Letter\GrabOutcomeCollector;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(GrabOutcomeCollector::class)]
final class GrabOutcomeCollectorTest extends TestCase
{
    public function testAWorseOutcomeIsNeverPaperedOverByALaterSuccess(): void
    {
        $collector = new GrabOutcomeCollector();

        // The same token can appear in several sections; if it came back blank in any of
        // them the letter has a hole, however well it resolved elsewhere.
        $collector->record('OP_NAME', GrabOutcomeCollector::EMPTY);
        $collector->record('OP_NAME', GrabOutcomeCollector::RESOLVED);

        $this->assertSame(['OP_NAME'], $collector->tokensWith(GrabOutcomeCollector::EMPTY));
        $this->assertSame([], $collector->tokensWith(GrabOutcomeCollector::RESOLVED));
    }

    public function testOutcomesBucketByKind(): void
    {
        $collector = new GrabOutcomeCollector();
        $collector->record('OP_NAME', GrabOutcomeCollector::RESOLVED);
        $collector->record('CORRESPONDENCE_ADDRESS', GrabOutcomeCollector::EMPTY);
        $collector->record('MADE_UP_TOKEN', GrabOutcomeCollector::UNKNOWN);

        $this->assertSame(['OP_NAME'], $collector->tokensWith(GrabOutcomeCollector::RESOLVED));
        $this->assertSame(['CORRESPONDENCE_ADDRESS'], $collector->tokensWith(GrabOutcomeCollector::EMPTY));
        $this->assertSame(['MADE_UP_TOKEN'], $collector->tokensWith(GrabOutcomeCollector::UNKNOWN));
    }
}

<?php

declare(strict_types=1);

/**
 * Queue Retry command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\Command\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Retry;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * Queue Retry command test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RetryTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $item = new QueueEntity();
        $command = Retry::create(['item' => $item, 'retryAfter' => 60, 'lastError' => 'last error']);

        $this->assertSame($item, $command->getItem());
        $this->assertEquals(60, $command->getRetryAfter());
        $this->assertEquals('last error', $command->getLastError());
        $this->assertEquals(
            ['item' => $item, 'retryAfter' => 60, 'lastError' => 'last error'],
            $command->getArrayCopy()
        );
    }
}

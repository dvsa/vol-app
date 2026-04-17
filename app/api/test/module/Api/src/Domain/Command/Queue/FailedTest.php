<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Command\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Failed;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Command\Queue\Failed::class)]
class FailedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $item = new QueueEntity();
        $lastErr = 'unit_LastErrMsg';

        $command = Failed::create(
            [
                'item' => $item,
                'lastError' => $lastErr,
            ]
        );

        $this->assertSame($item, $command->getItem());
        static::assertSame($lastErr, $command->getLastError());
        $this->assertEquals(
            [
                'item' => $item,
                'lastError' => $lastErr,
            ],
            $command->getArrayCopy()
        );
    }
}

<?php

namespace Dvsa\OlcsTest\Api\Domain\Command\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Failed;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Command\Queue\Failed
 */
class FailedTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
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

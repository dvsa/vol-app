<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\Command\MessageQueue;

use Dvsa\Olcs\Cli\Domain\Command\MessageQueue\Enqueue;
use PHPUnit\Framework\TestCase;

class EnqueueTest extends TestCase
{
    public function testStructure(): void
    {
        $sut = new Enqueue();
        $dto = $sut::create([
            'messageData' => [[123], [345]],
            'messageType' => 'SomeMessage',
            'queueType' => 'SomeQueue'
        ]);

        $this->assertEquals([[123], [345]], $dto->getMessageData());
        $this->assertEquals('SomeMessage', $dto->getMessageType());
        $this->assertEquals('SomeQueue', $dto->getQueueType());
    }
}

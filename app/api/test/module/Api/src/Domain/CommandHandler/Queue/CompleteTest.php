<?php

declare(strict_types=1);

/**
 * Queue Complete Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Queue;

use Dvsa\Olcs\Api\Domain\Command\Queue\Complete as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Queue\Complete;
use Dvsa\Olcs\Api\Domain\Repository\Queue as Repo;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Queue\Delete as DeleteQueueCmd;

/**
 *  Queue Complete Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Complete();
        $this->mockRepo('Queue', Repo::class);

        $this->refData = [
            QueueEntity::STATUS_COMPLETE,
        ];

        parent::setUp();
    }

    /**
     * Test handleCommand method
     */
    public function testHandleCommand(): void
    {
        $id = 1234;

        $item = new QueueEntity();
        $item->setId($id);
        $command = Cmd::create(['item' => $item]);

        $this->repoMap['Queue']
            ->shouldReceive('save')
            ->once()
            ->with($item);

        $this->expectedSideEffect(DeleteQueueCmd::class, ['id' => $id], new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Queue item marked complete'], $result->getMessages());

        $this->assertEquals(QueueEntity::STATUS_COMPLETE, $item->getStatus()->getId());
    }
}

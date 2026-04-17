<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Repository\Queue as QueueRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\EnqueueContinuationNotSought;
use Dvsa\Olcs\Api\Domain\Command\Licence\EnqueueContinuationNotSought as Cmd;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Enqueue CNS Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class EnqueueContinuationNotSoughtTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EnqueueContinuationNotSought();
        $this->mockRepo('Queue', QueueRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            QueueEntity::STATUS_QUEUED,
            QueueEntity::TYPE_CNS_EMAIL
        ];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licences' => ['foo'],
            'date' => 'bar'
        ];

        $command = Cmd::create($data);

        /** @var QueueEntity $savedQueue */
        $savedQueue = null;

        $this->repoMap['Queue']->shouldReceive('enqueueContinuationNotSought')
            ->with(['foo'])
            ->andReturn(3)
            ->once()
            ->shouldReceive('save')
            ->with(m::type(QueueEntity::class))
            ->andReturnUsing(
                function (QueueEntity $queue) use (&$savedQueue, $data) {
                    $queue->setType($this->refData[QueueEntity::TYPE_CNS_EMAIL]);
                    $queue->setStatus($this->refData[QueueEntity::STATUS_QUEUED]);
                    $queue->setOptions(json_encode($data));
                    $savedQueue = $queue;
                }
            )
            ->once();

        $expected = [
            'id' => [],
            'messages' => [
                'Enqueued 3 CNS messages',
                'Send CNS email message enqueued'
            ]
        ];

        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expected, $result->toArray());
        $this->assertEquals($savedQueue->getType(), $this->refData[QueueEntity::TYPE_CNS_EMAIL]);
        $this->assertEquals($savedQueue->getStatus(), $this->refData[QueueEntity::STATUS_QUEUED]);
        $this->assertEquals($savedQueue->getOptions(), json_encode($data));
    }
}

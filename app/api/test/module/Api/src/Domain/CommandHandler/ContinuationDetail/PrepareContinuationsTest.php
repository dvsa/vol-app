<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\PrepareContinuations as CommandHandler;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\PrepareContinuations as Command;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as ContinuationDetailRepo;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetaillEntityService;
use Mockery as m;

/**
 * Prepare continuations test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrepareContinuationsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', ContinuationDetailRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContinuationDetaillEntityService::STATUS_PRINTING
        ];
        $this->references = [];
        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $continuationDetailId = 1;
        $data = [
            'ids' => [$continuationDetailId]
        ];
        $command = Command::create($data);

        $mockContinuationDetail = m::mock()
            ->shouldReceive('setStatus')
            ->with($this->refData[ContinuationDetaillEntityService::STATUS_PRINTING])
            ->once()
            ->getMock();

        $this->repoMap['ContinuationDetail']
            ->shouldReceive('fetchById')
            ->with($continuationDetailId)
            ->andReturn($mockContinuationDetail)
            ->once()
            ->shouldReceive('save')
            ->with($mockContinuationDetail)
            ->once()
            ->getMock();

        $queueLettersResult = new Result();
        $queueLettersResult->addId('queue', 1);
        $queueLettersResult->addMessage('Queue created');

        $params = [
            'entityId' => 1,
            'type' => QueueEntity::TYPE_CONT_CHECKLIST,
            'status' => QueueEntity::STATUS_QUEUED
        ];
        $this->expectedSideEffect(CreateQueueCmd::class, $params, $queueLettersResult);

        $result = $this->sut->handleCommand($command);
        $messages = [
            'Queue created',
            'All letters queued'
        ];
        $this->assertEquals($messages, $result->getMessages());
        $this->assertEquals(['queue' => 1], $result->getIds());
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueueCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail\Queue as CommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Queue\Queue as QueueEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\Queue as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Queue letters test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class QueueTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ContinuationDetail', \Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [1],
            'type' => QueueEntity::STATUS_QUEUED
        ];
        $command = Command::create($data);

        $queueLettersResult = new Result();
        $queueLettersResult->addId('queue', 1);
        $queueLettersResult->addMessage('Queue created');

        $queueParams = [
            'entityId' => 1,
            'type' => $command->getType(),
            'status' => QueueEntity::STATUS_QUEUED
        ];
        $this->expectedSideEffect(CreateQueueCmd::class, $queueParams, $queueLettersResult);

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(['Queue created', 'All letters queued'], $result->getMessages());
        $this->assertEquals(['queue' => 1], $result->getIds());
    }
}

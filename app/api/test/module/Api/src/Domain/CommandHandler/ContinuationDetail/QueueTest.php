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

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [1],
            'type' => QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER
        ];
        $command = Command::create($data);

        $queueLettersResult = new Result();
        $queueLettersResult->addId('queue', 1);
        $queueLettersResult->addMessage('Queue created');

        $queueParams = [
            'entityId' => 1,
            'type' => QueueEntity::TYPE_CONT_CHECKLIST_REMINDER_GENERATE_LETTER,
            'status' => QueueEntity::STATUS_QUEUED
        ];
        $this->expectedSideEffect(CreateQueueCmd::class, $queueParams, $queueLettersResult);

        $continuationDetail = m::mock(ContinuationDetail::class);
        $continuationDetail->shouldReceive('getLicence->getId')
            ->andReturn('7');

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchById')
            ->with(1)
            ->andReturn($continuationDetail);

        $user = m::mock(User::class);
        $user->shouldReceive('getId')
            ->andReturn(77);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $createTaskResult = new Result();
        $createTaskResult->addId('task', 1);
        $createTaskResult->addMessage('Task created successfully');

        $taskParams = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS,
            'description' => 'Check if checklist has been received',
            'actionDate' => (new DateTime('+14 days'))->format('Y-m-d'),
            'licence' => 7,
            'assignedToUser' => 77,
        ];
        $this->expectedSideEffect(CreateTask::class, $taskParams, $createTaskResult);

        $result = $this->sut->handleCommand($command);
        $messages = [
            'Queue created',
            'Task created successfully',
            'All letters queued'
        ];
        $this->assertEquals($messages, $result->getMessages());
        $this->assertEquals(['queue' => 1, 'task' => 1], $result->getIds());
    }
}

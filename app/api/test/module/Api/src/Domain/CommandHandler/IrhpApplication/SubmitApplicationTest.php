<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType as EventHistoryTypeEntity;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Service\EventHistory\Creator as EventHistoryCreator;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CreateTaskCommandGenerator;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Mockery as m;

class SubmitApplicationTest extends AbstractCommandHandlerTestCase
{
    public const TASK_CREATION_MESSAGE = 'Task created';
    public const SUBMISSION_TASK_DESCRIPTION = 'Submission task description';

    public const IRHP_APPLICATION_ID = 44;
    public const LICENCE_ID = 7;
    public const IRHP_PERMIT_TYPE_ID = 11;

    private $irhpApplication;

    private $command;

    private $expectedTaskParams;

    private $expectedMessages;

    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->sut = new SubmitApplication();

        $this->mockedSmServices = [
            'PermitsCheckableCreateTaskCommandGenerator' => m::mock(CreateTaskCommandGenerator::class),
            'EventHistoryCreator' => m::mock(EventHistoryCreator::class),
        ];

        $this->irhpApplication = m::mock(IrhpApplication::class);
        $this->irhpApplication->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(self::IRHP_APPLICATION_ID);
        $this->irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->withNoArgs()
            ->andReturn(self::IRHP_PERMIT_TYPE_ID);
        $this->irhpApplication->shouldReceive('getSubmissionTaskDescription')
            ->withNoArgs()
            ->andReturn(self::SUBMISSION_TASK_DESCRIPTION);
        $this->irhpApplication->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn(self::LICENCE_ID);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->with(self::IRHP_APPLICATION_ID)
            ->andReturn($this->irhpApplication);

        $this->command = m::mock(CommandInterface::class);
        $this->command->shouldReceive('getId')
            ->andReturn(self::IRHP_APPLICATION_ID);

        $this->expectedTaskParams = [
            'category' => Task::CATEGORY_PERMITS,
            'subCategory' => Task::SUBCATEGORY_APPLICATION,
            'description' => self::SUBMISSION_TASK_DESCRIPTION,
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'licence' => self::LICENCE_ID,
        ];

        $expectedTask = CreateTask::create($this->expectedTaskParams);

        $this->mockedSmServices['PermitsCheckableCreateTaskCommandGenerator']->shouldReceive('generate')
            ->with($this->irhpApplication)
            ->andReturn($expectedTask);

        $this->expectedMessages = [
            self::TASK_CREATION_MESSAGE,
            'IRHP application submitted'
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_ISSUING,
            IrhpInterface::STATUS_UNDER_CONSIDERATION
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSubmissionStatuses')]
    public function testHandleCommandWithAllocation(mixed $submissionStatus): void
    {
        $this->irhpApplication->shouldReceive('shouldAllocatePermitsOnSubmission')
            ->withNoArgs()
            ->andReturn(true);
        $this->irhpApplication->shouldReceive('getSubmissionStatus')
            ->withNoArgs()
            ->andReturn($submissionStatus);
        $this->irhpApplication->shouldReceive('submit')
            ->with($this->refData[$submissionStatus])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($this->irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($this->irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_SUBMITTED)
            ->once();

        $this->expectedQueueSideEffect(
            self::IRHP_APPLICATION_ID,
            Queue::TYPE_IRHP_APPLICATION_PERMITS_ALLOCATE,
            []
        );

        $this->expectedQueueSideEffect(
            self::IRHP_APPLICATION_ID,
            Queue::TYPE_PERMITS_POST_SUBMIT,
            ['irhpPermitType' => self::IRHP_PERMIT_TYPE_ID]
        );

        $this->expectedSideEffect(
            CreateTask::class,
            $this->expectedTaskParams,
            (new Result())->addMessage(self::TASK_CREATION_MESSAGE)
        );

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->expectedMessages,
            $result->getMessages()
        );

        $this->assertEquals(self::IRHP_APPLICATION_ID, $result->getId('irhpApplication'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSubmissionStatuses')]
    public function testHandleCommandWithoutAllocation(mixed $submissionStatus): void
    {
        $this->irhpApplication->shouldReceive('shouldAllocatePermitsOnSubmission')
            ->withNoArgs()
            ->andReturn(false);
        $this->irhpApplication->shouldReceive('getSubmissionStatus')
            ->withNoArgs()
            ->andReturn($submissionStatus);
        $this->irhpApplication->shouldReceive('submit')
            ->with($this->refData[$submissionStatus])
            ->once()
            ->globally()
            ->ordered();
        $this->repoMap['IrhpApplication']->shouldReceive('save')
            ->with($this->irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $this->mockedSmServices['EventHistoryCreator']->shouldReceive('create')
            ->with($this->irhpApplication, EventHistoryTypeEntity::IRHP_APPLICATION_SUBMITTED)
            ->once();

        $this->expectedQueueSideEffect(
            self::IRHP_APPLICATION_ID,
            Queue::TYPE_PERMITS_POST_SUBMIT,
            ['irhpPermitType' => self::IRHP_PERMIT_TYPE_ID]
        );

        $this->expectedSideEffect(
            CreateTask::class,
            $this->expectedTaskParams,
            (new Result())->addMessage(self::TASK_CREATION_MESSAGE)
        );

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            $this->expectedMessages,
            $result->getMessages()
        );

        $this->assertEquals(self::IRHP_APPLICATION_ID, $result->getId('irhpApplication'));
    }

    public static function dpSubmissionStatuses(): array
    {
        return [
            [IrhpInterface::STATUS_ISSUING],
            [IrhpInterface::STATUS_UNDER_CONSIDERATION],
        ];
    }
}

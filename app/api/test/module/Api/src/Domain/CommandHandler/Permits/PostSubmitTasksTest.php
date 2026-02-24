<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Permits;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEcmtShortTermAppSubmitted as SendEcmtShortTermAppSubmittedCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpApplication\StoreSnapshot as IrhpApplicationSnapshotCmd;
use Dvsa\Olcs\Api\Domain\Command\Permits\PostSubmitTasks as PostSubmitTasksCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Permits\PostSubmitTasks;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\IrhpCandidatePermitsCreator;
use Mockery as m;

class PostSubmitTasksTest extends AbstractCommandHandlerTestCase
{
    public const IRHP_PERMIT_TYPE_ID = 15;

    public const IRHP_APPLICATION_ID = 100;

    private $irhpApplication;

    public function setUp(): void
    {
        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        $this->mockedSmServices = [
            'PermitsCandidatePermitsIrhpCandidatePermitsCreator' => m::mock(IrhpCandidatePermitsCreator::class),
        ];

        $this->sut = new PostSubmitTasks();

        $this->irhpApplication = m::mock(IrhpApplication::class);

        parent::setUp();
    }

    public function testHandleCommandForIrhpWithoutAppSubmittedEmail(): void
    {
        $this->irhpApplication->shouldReceive('getAppSubmittedEmailCommand')
            ->andReturn(null);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->once()
            ->with(self::IRHP_APPLICATION_ID)
            ->andReturn($this->irhpApplication);

        $this->mockedSmServices['PermitsCandidatePermitsIrhpCandidatePermitsCreator']->shouldReceive('createIfRequired')
            ->with($this->irhpApplication)
            ->once();

        $this->expectedSideEffect(
            IrhpApplicationSnapshotCmd::class,
            [
                'id' => self::IRHP_APPLICATION_ID,
            ],
            (new Result())->addMessage('Snapshot created')
        );

        $result = $this->sut->handleCommand(
            PostSubmitTasksCmd::create(
                [
                    'id' => self::IRHP_APPLICATION_ID,
                    'irhpPermitType' => self::IRHP_PERMIT_TYPE_ID,
                ]
            )
        );

        $this->assertEquals(
            [
                'Snapshot created'
            ],
            $result->getMessages()
        );
    }

    public function testHandleCommandForIrhpWithAppSubmittedEmail(): void
    {
        $this->irhpApplication->shouldReceive('getAppSubmittedEmailCommand')
            ->andReturn(SendEcmtShortTermAppSubmittedCmd::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchById')
            ->once()
            ->with(self::IRHP_APPLICATION_ID)
            ->andReturn($this->irhpApplication);

        $this->mockedSmServices['PermitsCandidatePermitsIrhpCandidatePermitsCreator']->shouldReceive('createIfRequired')
            ->with($this->irhpApplication)
            ->once();

        $this->expectedSideEffect(
            IrhpApplicationSnapshotCmd::class,
            [
                'id' => self::IRHP_APPLICATION_ID,
            ],
            (new Result())->addMessage('Snapshot created')
        );

        $this->expectedEmailQueueSideEffect(
            SendEcmtShortTermAppSubmittedCmd::class,
            ['id' => self::IRHP_APPLICATION_ID],
            self::IRHP_APPLICATION_ID,
            new Result()
        );

        $result = $this->sut->handleCommand(
            PostSubmitTasksCmd::create(
                [
                    'id' => self::IRHP_APPLICATION_ID,
                    'irhpPermitType' => self::IRHP_PERMIT_TYPE_ID,
                ]
            )
        );

        $this->assertEquals(
            [
                'Snapshot created'
            ],
            $result->getMessages()
        );
    }
}

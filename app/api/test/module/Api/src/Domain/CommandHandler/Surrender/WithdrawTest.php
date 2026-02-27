<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\Withdraw as WithdrawHandler;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Clear as ClearCommand;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Surrender as SurrenderRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Surrender as SurrenderEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateCommand;
use Dvsa\Olcs\Transfer\Command\Surrender\Withdraw as WithdrawCommand;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

class WithdrawTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var WithdrawHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new WithdrawHandler();
        $this->mockRepo('Surrender', SurrenderRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Task', TaskRepo::class);
        $this->mockRepo('EventHistory', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('EventHistoryType', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->refData = [];
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];
        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::SURRENDER_STATUS_WITHDRAWN,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 111;

        $this->expectedSideEffect(
            UpdateCommand::class,
            [
                'id' => $licenceId,
                'status' => RefData::SURRENDER_STATUS_WITHDRAWN
            ],
            new Result()
        );

        $this->expectedSideEffect(
            ClearCommand::class,
            [
                'id' => $licenceId
            ],
            new Result()
        );

        $surrenderEntity = new SurrenderEntity();
        $surrenderEntity->setId(1);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->with($licenceId, 1)
            ->once()
            ->andReturn($surrenderEntity);

        $taskEntity = m::mock(TaskEntity::class);
        $taskEntity->shouldReceive('getId')
            ->once()
            ->andReturn(11);

        $this->repoMap['Task']
            ->shouldReceive('fetchOpenTasksForSurrender')
            ->with(1)
            ->once()
            ->andReturn([$taskEntity]);

        $this->expectedSideEffect(
            CloseTasks::class,
            [
                'ids' => [11]
            ],
            new Result()
        );

        /** @var UserEntity $user */
        $loggedInUser = m::mock(UserEntity::class)->makePartial();
        $loggedInUserId = 1000;
        $loggedInUser->setId($loggedInUserId);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->once()
            ->andReturn($loggedInUser);

        $eventHistoryType = new EventHistoryType();
        $this->repoMap['EventHistoryType']
            ->shouldReceive('fetchOneByEventCode')
            ->with(EventHistoryType::EVENT_CODE_SURRENDER_APPLICATION_WITHDRAWN)
            ->andReturn($eventHistoryType);

        $this->repoMap['EventHistory']
            ->shouldReceive('save')
            ->once();

        $licence = $this->getTestingLicence();
        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->with(m::type(LicenceEntity::class))
            ->once();

        $this->queryHandler
            ->shouldReceive('handleQuery')
            ->once()
            ->andReturn(['status' => 'licence_status']);

        $command = WithdrawCommand::create(['id' => $licenceId]);
        $this->sut->handleCommand($command);
    }
}

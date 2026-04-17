<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Surrender;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Surrender\Snapshot;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\CommandHandler\Surrender\SubmitForm as sut;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\EventHistory\EventHistoryType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

class SubmitFormTest extends AbstractCommandHandlerTestCase
{
    /** @var Sut */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->refData = [];
        $this->mockRepo('Surrender', \Dvsa\Olcs\Api\Domain\Repository\Surrender::class);
        $this->mockRepo('Licence', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('EventHistory', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockRepo('EventHistoryType', \Dvsa\Olcs\Api\Domain\Repository\Licence::class);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            RefData::SIG_PHYSICAL_SIGNATURE,
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 65,
        ];
        $command = Cmd::create($data);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Surrender\Update::class,
            [
                'signatureType' => RefData::SIG_PHYSICAL_SIGNATURE,
                'id' => 65,
                'status' => Surrender::SURRENDER_STATUS_SIGNED,
            ],
            new Result()
        );

        $surrenderEntity = m::mock(Surrender::class);

        $this->repoMap['Surrender']
            ->shouldReceive('fetchOneByLicenceId')
            ->with($command->getId(), Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($surrenderEntity);

        $surrenderEntity->shouldReceive('getId')
            ->andReturn(5);

        $this->expectedSideEffect(
            Snapshot::class,
            [
                'id' => $command->getId()
            ],
            new Result()
        );

        $actionDate = new DateTime();
        $actionDate->add(new \DateInterval('P14D'));

        $this->expectedSideEffect(
            CreateTask::class,
            [
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
                'description' => 'Digital surrender',
                'isClosed' => 'N',
                'urgent' => 'N',
                'licence' => $command->getId(),
                'surrender' => 5,
                'actionDate' => $actionDate->format('Y-m-d')
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
            ->with(EventHistoryType::EVENT_CODE_SURRENDER_UNDER_CONSIDERATION)
            ->andReturn($eventHistoryType);

        $this->repoMap['EventHistory']
            ->shouldReceive('save')
            ->once();

        $licence = m::mock(Licence::class)
            ->shouldReceive('setStatus')
            ->once()
            ->shouldReceive('getId')
            ->andReturn(1)
            ->getMock();

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->with(65)
            ->once()
            ->andReturn($licence);

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->with($licence)
            ->once();

        $this->sut->handleCommand($command);
    }
}

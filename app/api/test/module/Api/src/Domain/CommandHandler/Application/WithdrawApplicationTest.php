<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Command\Licence\ReturnAllCommunityLicences;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result as QueueResult;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeTransaction;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\WithdrawApplication as CommandHandler;
use Dvsa\Olcs\Transfer\Command\Application\WithdrawApplication as Command;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Domain\Command\Discs\CeaseGoodsDiscsForApplication;
use Dvsa\Olcs\Api\Domain\Command\Licence\Withdraw;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as EndInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Withdraw Application Test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class WithdrawApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            'apsts_withdrawn',
            'withdrawn'
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(false);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $licence = m::mock(Licence::class)
            ->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getCommunityLics')
            ->andReturn(
                m::mock()
                    ->shouldReceive('count')
                    ->once()
                    ->andReturn(3)
                    ->getMock()
            )
            ->shouldReceive('getTrafficArea')->with()->once()->andReturn($trafficArea)
            ->getMock();

        $application = m::mock(Application::class)->makePartial();
        $application->setId(1);
        $application->setLicence($licence);

        $application->shouldReceive('getCurrentInterimStatus')
            ->andReturn(Application::INTERIM_STATUS_INFORCE)
            ->twice()
            ->shouldReceive('isGoods')->andReturn(true)
            ->getMock();

        $this->expectedSideEffect(
            EndInterimCmd::class,
            ['id' => 1],
            new Result(),
            1
        );

        $application->shouldReceive('getIsVariation')->andReturn(false);

        $application->shouldReceive('isPreviouslyPublished')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(Withdraw::class, ['id' => 123], new Result());
        $this->expectedSideEffect(CeaseGoodsDiscsForApplication::class, ['application' => 1], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 1, 'trafficArea' => 'TA'],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\Schedule41Cancel::class,
            ['id' => 1],
            new Result()
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $data = ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW];
        $this->expectedSideEffect(CreateSnapshot::class, $data, $result1);

        $this->expectedSideEffect(ReturnAllCommunityLicences::class, ['id' => 123], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new QueueResult()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Snapshot created', 'Application 1 withdrawn.'], $result->getMessages());
    }

    public function testHandleCommandCloseTasks(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(true);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $licence = m::mock(Licence::class)
            ->shouldReceive('getId')
            ->andReturn(123)
            ->shouldReceive('getCommunityLics')
            ->andReturn(
                m::mock()
                    ->shouldReceive('count')
                    ->once()
                    ->andReturn(3)
                    ->getMock()
            )
            ->shouldReceive('getTrafficArea')->with()->once()->andReturn($trafficArea)
            ->getMock();

        $application = m::mock(Application::class)->makePartial();
        $application->setId(1);
        $application->setLicence($licence);

        $application->shouldReceive('getCurrentInterimStatus')
            ->andReturn(Application::INTERIM_STATUS_INFORCE)
            ->twice()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->getMock();
        $this->expectedSideEffect(EndInterimCmd::class, ['id' => 1], new Result());

        $application->shouldReceive('getIsVariation')->andReturn(false);

        $application->shouldReceive('isPreviouslyPublished')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(Withdraw::class, ['id' => 123], new Result());
        $this->expectedSideEffect(CeaseGoodsDiscsForApplication::class, ['application' => 1], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 1, 'trafficArea' => 'TA'],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 1],
            new Result()
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $data = ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW];
        $this->expectedSideEffect(CreateSnapshot::class, $data, $result1);

        $this->expectedSideEffect(ReturnAllCommunityLicences::class, ['id' => 123], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 1],
            (new Result())->addMessage('CLOSE_TEX_TASK')
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseFeeDueTask::class,
            ['id' => 1],
            (new Result())->addMessage('CLOSE_FEEDUE_TASK')
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\Schedule41Cancel::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new QueueResult()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            [
                'Snapshot created',
                'CLOSE_TEX_TASK',
                'CLOSE_FEEDUE_TASK',
                'Application 1 withdrawn.'
            ],
            $result->getMessages()
        );
    }

    public function testHandleCommandVariationNotPublishable(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(false);

        $licence = $this->getTestingLicence()
            ->setLicenceType(new \Dvsa\Olcs\Api\Entity\System\RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $licence->setId(123);

        $application = $this->getTestingApplication($licence)
            ->setId(1)
            ->setIsVariation(true)
            ->setLicenceType(new \Dvsa\Olcs\Api\Entity\System\RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));

        $this->repoMap['Application']->shouldReceive('fetchById')->with(532)->andReturn($application);
        $this->repoMap['Application']->shouldReceive('save')->once()->with(m::type(Application::class));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW],
            (new Result())->addMessage('Snapshot created')
        );
        $this->expectedSideEffect(
            CeaseGoodsDiscsForApplication::class,
            ['application' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new QueueResult()
        );

        $result = $this->sut->handleCommand($command);
        $this->assertSame(['Snapshot created', 'Application 1 withdrawn.'], $result->getMessages());
    }

    public function testHandleCommandVariationPublishable(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(false);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $licence = $this->getTestingLicence()
            ->setTrafficArea($trafficArea)
            ->setLicenceType(new \Dvsa\Olcs\Api\Entity\System\RefData(Licence::LICENCE_TYPE_STANDARD_NATIONAL));
        $licence->setId(123);

        $application = $this->getTestingApplication($licence)
            ->setId(1)
            ->setIsVariation(true)
            ->setLicenceType(new \Dvsa\Olcs\Api\Entity\System\RefData(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL));

        $publicationLink = m::mock(PublicationLinkEntity::class)
            ->shouldReceive('getpublicationSection')
            ->andReturn(PublicationSectionEntity::APP_NEW_SECTION)
            ->getMock();

        $publicationLinks = new ArrayCollection();
        $publicationLinks->add($publicationLink);

        $application->setPublicationLinks($publicationLinks);

        $this->repoMap['Application']->shouldReceive('fetchById')->with(532)->andReturn($application);
        $this->repoMap['Application']->shouldReceive('save')->once()->with(m::type(Application::class));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(
            CreateSnapshot::class,
            ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW],
            (new Result())->addMessage('Snapshot created')
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            ['id' => 1, 'trafficArea' => 'TA'],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            ['id' => 1],
            new Result()
        );
        $this->expectedSideEffect(
            CeaseGoodsDiscsForApplication::class,
            ['application' => 1],
            new Result()
        );
        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new QueueResult()
        );

        $result = $this->sut->handleCommand($command);
        $this->assertSame(['Snapshot created', 'Application 1 withdrawn.'], $result->getMessages());
    }

    public function testHandleCommandVariation(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(false);

        $trafficArea = new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea();
        $trafficArea->setId('TA');

        $licence = m::mock(Licence::class)->shouldReceive('getId')->with()->andReturn(123);

        $application = m::mock(Application::class)->makePartial();
        $application->setId(1);
        $application->setLicence($licence);
        $application->setIsVariation(true);

        $application
            ->shouldReceive('getCurrentInterimStatus')->with()->twice()->andReturn(Application::INTERIM_STATUS_INFORCE)
            ->shouldReceive('isGoods')->andReturn(true)
            ->shouldReceive('isPublishable')->with()->once()->andReturn(false);

        $this->expectedSideEffect(EndInterimCmd::class, ['id' => 1], new Result());

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(CeaseGoodsDiscsForApplication::class, ['application' => 1], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\Schedule41Cancel::class,
            ['id' => 1],
            new Result()
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $data = ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW];
        $this->expectedSideEffect(CreateSnapshot::class, $data, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new QueueResult()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Snapshot created', 'Application 1 withdrawn.'], $result->getMessages());
    }

    public function testHandleCommandRefund(): void
    {
        $command = Command::create(['id' => 532, 'reason' => 'withdrawn']);

        $this->setupIsInternalUser(false);

        $application = m::mock(Application::class)->makePartial();
        $application->shouldReceive('getId')->times(4)->andReturn(1);
        $application->shouldReceive('isGoods')->andReturn(true)
            ->shouldReceive('isPublishable')->with()->once()->andReturn(false)
            ->shouldReceive('isNew')->times(2)->andReturn(false)
            ->shouldReceive('getCurrentInterimStatus')->andReturn(Application::INTERIM_STATUS_REQUESTED);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(532)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Application::class));

        $feeEntity = m::mock(Fee::class);
        $feeEntity->shouldReceive('getFeeType')->andReturn(
            m::mock(FeeType::class)->shouldReceive('isInterimGrantFee')
                ->once()
                ->andReturnTrue()
                ->getMock()
        );
        $feeEntity->shouldReceive('canRefund')->andReturnTrue();
        $feeEntity->shouldReceive('getId')->andReturn(1);

        $application->setFees(new ArrayCollection([$feeEntity]));

        $this->repoMap['LicenceVehicle']->shouldReceive('clearSpecifiedDateAndInterimApp')
            ->with($application)
            ->once()
            ->getMock();

        $this->expectedSideEffect(CeaseGoodsDiscsForApplication::class, ['application' => 1], new Result());

        $this->expectedSideEffect(
            \Dvsa\Olcs\Transfer\Command\Application\Schedule41Cancel::class,
            ['id' => 1],
            new Result()
        );

        $result1 = new Result();
        $result1->addMessage('Snapshot created');
        $data = ['id' => 532, 'event' => CreateSnapshot::ON_WITHDRAW];
        $this->expectedSideEffect(CreateSnapshot::class, $data, $result1);

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 1],
            new Result()
        );

        $this->expectedSideEffect(
            Create::class,
            [
                'entityId' => 1,
                'type' => Queue::TYPE_REFUND_INTERIM_FEES,
                'status' => Queue::STATUS_QUEUED
            ],
            new Result()
        );

        $this->expectedSideEffect(
            UpdateFeeStatus::class,
            [
                'id' => $feeEntity->getId(),
                'status' => Fee::STATUS_REFUND_PENDING
            ],
            new Result()
        );

        $this->sut->handleCommand($command);
    }
}

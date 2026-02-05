<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\EndInterim as EndInterimCmd;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CommonGrant;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\CreateDiscRecords;
use Dvsa\Olcs\Api\Domain\Command\Application\Grant\ProcessApplicationOperatingCentres;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateSmallVehicleCondition as CreateSvConditionUndertakingCmd;
use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\Grant as GrantVariartionCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadVariationTypeException;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Transfer\Command\Application\CreateSnapshot;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvDiscs;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Grant Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantVariartionCommandHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);
        $this->mockRepo('GoodsDisc', \Dvsa\Olcs\Api\Domain\Repository\GoodsDisc::class);
        $this->mockRepo('PsvDisc', \Dvsa\Olcs\Api\Domain\Repository\GoodsDisc::class);
        $this->mockRepo('LicenceVehicle', \Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ApplicationEntity::APPLICATION_STATUS_VALID,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            ApplicationEntity::VARIATION_TYPE_DIRECTOR_CHANGE,
            RefData::GRANT_AUTHORITY_DELEGATED
        ];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 111,
            'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->updateTotAuthHgvVehicles(10);
        $licence->setTrafficArea((new \Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea())->setId('TA'));

        $vehicle = m::mock();

        $existingLicenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $existingLicenceVehicle->setVehicle($vehicle);
        $existingLicenceVehicle->setRemovalDate(null);
        $existingLicenceVehicle->setSpecifiedDate(new \DateTime());

        $existingLicenceVehicles = new ArrayCollection();
        $existingLicenceVehicles->add($existingLicenceVehicle);
        $licence->setLicenceVehicles($existingLicenceVehicles);

        /* @var $application ApplicationEntity */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $application->setId(111);
        $application->setLicence($licence);

        $newLicenceVehicles = new ArrayCollection();
        $newLicenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $newLicenceVehicle->setVehicle($vehicle);
        $newLicenceVehicles->add($newLicenceVehicle);

        $application->shouldReceive('getCurrentInterimStatus')
            ->andReturn(ApplicationEntity::INTERIM_STATUS_INFORCE)
            ->twice()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->twice()
            ->shouldReceive('isVariation')
            ->andReturn(true)
            ->once()
            ->shouldReceive('isPublishable')
            ->andReturn(true)
            ->once()
            ->shouldReceive('isPsv')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getLicenceVehicles')
            ->andReturn($newLicenceVehicles)
            ->once()
            ->shouldReceive('setGrantAuthority')
            ->with(m::on(fn($refData) => $refData instanceof RefData && $refData->getId() === RefData::GRANT_AUTHORITY_DELEGATED))
            ->once()
            ->getMock();

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('save')
            ->with($newLicenceVehicle)
            ->getMock();

        $this->expectedSideEffectAsSystemUser(EndInterimCmd::class, ['id' => 111], new Result());

        $licence->shouldReceive('copyInformationFromApplication')
            ->with($application);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Transfer\Command\Publication\Application::class,
            [
                'id' => 111,
                'trafficArea' => 'TA',
                'publicationSection' => \Dvsa\Olcs\Api\Entity\Publication\PublicationSection::VAR_GRANTED_SECTION,
            ],
            new Result()
        );
        $this->expectedSideEffectAsSystemUser(
            \Dvsa\Olcs\Api\Domain\Command\Application\CloseTexTask::class,
            [
                'id' => 111,
            ],
            new Result()
        );

        $result2 = new Result();
        $result2->addMessage('CreateDiscRecords');
        $expectedCommandData = ['id' => $data['id']];
        $expectedCommandData['currentTotAuth'] = 10;
        $this->expectedSideEffectAsSystemUser(CreateDiscRecords::class, $expectedCommandData, $result2);

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, ['id' => $data['id']], $result3);

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $expectedCommandData = ['id' => $data['id']];
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, $expectedCommandData, $result4);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 111
            ],
            'messages' => [
                'CreateSnapshot',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'Application 111 granted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }

    public function testHandleCommandUpgradeGoods(): void
    {
        $data = [
            'id' => 111,
            'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->updateTotAuthHgvVehicles(10);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('isGoods')
            ->andReturn(true)
            ->shouldReceive('isPublishable')
            ->andReturn(false)
            ->shouldReceive('isPsv')
            ->once()
            ->andReturn(false)
            ->shouldReceive('getLicenceVehicles')
            ->andReturn([])
            ->once()
            ->getMock();

        /** @var GoodsDisc $goodsDisc1 */
        $goodsDisc1 = m::mock(GoodsDisc::class)->makePartial();
        $goodsDisc1->setCeasedDate('2015-01-01');
        /** @var GoodsDisc $goodsDisc2 */
        $goodsDisc2 = m::mock(GoodsDisc::class)->makePartial();

        $goodsDiscs = [
            $goodsDisc1,
            $goodsDisc2
        ];

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setGoodsDiscs($goodsDiscs);

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle);

        $licence->shouldReceive('copyInformationFromApplication')
            ->with($application)
            ->shouldReceive('getLicenceVehicles->matching')
            ->andReturn($licenceVehicles);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['GoodsDisc']->shouldReceive('updateExistingGoodsDiscs')->with($application)->once()
            ->andReturn(41);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CreateDiscRecords');
        $discData = ['id' => $data['id']];
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffect(CreateDiscRecords::class, $discData, $result2);

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, ['id' => $data['id']], $result3);

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, ['id' => $data['id']], $result4);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 111
            ],
            'messages' => [
                'CreateSnapshot',
                '41 Goods Disc(s) replaced',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'Application 111 granted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }

    public function testHandleCommandUpgradePsv(): void
    {
        $data = [
            'id' => 111,
            'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->updateTotAuthHgvVehicles(10);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('isGoods')
            ->andReturn(false)
            ->shouldReceive('isPublishable')
            ->andReturn(false);

        $application->shouldReceive('isPsv')
            ->once()
            ->andReturn(true);
        $this->expectedSideEffectAsSystemUser(
            CreateSvConditionUndertakingCmd::class,
            ['applicationId' => 111],
            new Result()
        );

        $licence->shouldReceive('copyInformationFromApplication')
            ->with($application);
        $licence->shouldReceive('getPsvDiscsNotCeased->count')->with()->once()->andReturn(123);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $this->repoMap['PsvDisc']->shouldReceive('ceaseDiscsForLicence')->with(222)->once();

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CreateDiscRecords');
        $discData = ['id' => $data['id']];
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffectAsSystemUser(
            CreateDiscRecords::class,
            $discData,
            $result2
        );

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, ['id' => $data['id']], $result3);

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, ['id' => $data['id']], $result4);

        $result6 = new Result();
        $result6->addMessage('CreatePsvDiscs');
        $this->expectedSideEffect(
            CreatePsvDiscs::class,
            ['licence' => 222, 'amount' => 123, 'isCopy' => 'N'],
            $result6
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 111
            ],
            'messages' => [
                'CreateSnapshot',
                'CreatePsvDiscs',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'Application 111 granted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }

    public function testHandleCommandUpgradePsvNoPsvDiscs(): void
    {
        $data = [
            'id' => 111,
            'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
        ];

        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);
        $licence->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->updateTotAuthHgvVehicles(10);

        $licence->shouldReceive('getPsvDiscsNotCeased->count')->with()->once()->andReturn(0);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL]);
        $application->setId(111);
        $application->setLicence($licence);
        $application->shouldReceive('isGoods')
            ->andReturn(false)
            ->shouldReceive('isPublishable')
            ->andReturn(false)
            ->shouldReceive('isPsv')
            ->once()
            ->andReturn(true);
        $this->expectedSideEffectAsSystemUser(
            CreateSvConditionUndertakingCmd::class,
            ['applicationId' => 111],
            new Result()
        );

        $licence->shouldReceive('copyInformationFromApplication')
            ->with($application)
            ->shouldReceive('getPsvDiscs->matching')
            ->andReturn(null);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CreateDiscRecords');
        $discData = ['id' => $data['id']];
        $discData['currentTotAuth'] = 10;
        $this->expectedSideEffectAsSystemUser(
            CreateDiscRecords::class,
            $discData,
            $result2
        );

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(
            ProcessApplicationOperatingCentres::class,
            ['id' => $data['id']],
            $result3
        );

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, ['id' => $data['id']], $result4);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 111
            ],
            'messages' => [
                'CreateSnapshot',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'Application 111 granted'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(ApplicationEntity::APPLICATION_STATUS_VALID, $application->getStatus()->getId());
    }

    public function testThatIncorrectVariationTypeThrowsException(): void
    {
        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setVariationType($this->refData[ApplicationEntity::VARIATION_TYPE_DIRECTOR_CHANGE]);

        $command = Cmd::create(['id' => 'TEST_ID']);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->expectException(BadVariationTypeException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRefundInterim(): void
    {
        $data = [
            'id' => 111,
            'grantAuthority' => RefData::GRANT_AUTHORITY_DELEGATED,
        ];

        $command = Cmd::create($data);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getInterimStatus')
            ->once()
            ->andReturn(new RefData(ApplicationEntity::INTERIM_STATUS_REQUESTED));
        $application->setId($command->getId());
        $application->shouldReceive('setStatus')->with();
        $application->shouldReceive('isPublishable')->andReturnFalse();
        $application->shouldReceive('isNew')->andReturnFalse();
        $mockLicenceType = m::mock();
        $application->shouldReceive('getLicenceType')->andReturn($mockLicenceType);
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicenceType')->andReturn($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $licence->shouldReceive('getPsvDiscsNotCeased->count')->andReturn(0);
        $licence->shouldReceive('getTotAuthVehicles')->andReturn(1);
        $licence->shouldReceive('copyInformationFromApplication')->with($application);
        $application->shouldReceive('getLicence')->andReturn($licence);

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

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($command)->andReturn($application)
            ->shouldReceive('save')->with($application)->once();

        $result1 = new Result();
        $result1->addMessage('CreateSnapshot');
        $this->expectedSideEffectAsSystemUser(
            CreateSnapshot::class,
            ['id' => 111, 'event' => CreateSnapshot::ON_GRANT],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('CreateDiscRecords');
        $discData = ['id' => $data['id']];
        $discData['currentTotAuth'] = 1;
        $this->expectedSideEffectAsSystemUser(CreateDiscRecords::class, $discData, $result2);

        $result3 = new Result();
        $result3->addMessage('ProcessApplicationOperatingCentres');
        $this->expectedSideEffectAsSystemUser(ProcessApplicationOperatingCentres::class, ['id' => $data['id']], $result3);

        $result4 = new Result();
        $result4->addMessage('CommonGrant');
        $this->expectedSideEffectAsSystemUser(CommonGrant::class, ['id' => $data['id']], $result4);

        $interimFeeRefundQueueCmdData = [
            'entityId' => 1,
            'type' => Queue::TYPE_REFUND_INTERIM_FEES,
            'status' => Queue::STATUS_QUEUED,
        ];
        $result5 = new Result();
        $result5->addMessage('Message added to queue');
        $this->expectedSideEffectAsSystemUser(Create::class, $interimFeeRefundQueueCmdData, $result5);

        $this->expectedSideEffect(
            UpdateFeeStatus::class,
            [
                'id' => $feeEntity->getId(),
                'status' => Fee::STATUS_REFUND_PENDING
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'Application' => 111
            ],
            'messages' => [
                'CreateSnapshot',
                'Message added to queue',
                'CreateDiscRecords',
                'ProcessApplicationOperatingCentres',
                'CommonGrant',
                'Application 111 granted'
            ]
        ];

        $this->assertSame($expected, $result->toArray());
    }
}

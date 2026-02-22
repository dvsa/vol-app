<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\UpdateGoodsVehicle;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Transfer\Command\Vehicle\UpdateGoodsVehicle as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateGoodsVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateGoodsVehicle();
        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommandAttemptToUpdateRemovalDate(): void
    {
        $this->expectException(ForbiddenException::class);

        $data = [
            'removalDate' => '2015-01-01'
        ];
        $command = Cmd::create($data);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandAttemptToUpdateRemovalDateOnActiveRecord(): void
    {
        $this->expectException(BadRequestException::class);

        $data = [
            'removalDate' => '2015-01-01',
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandAttemptToUpdateRemovedRecord(): void
    {
        $this->expectException(BadRequestException::class);

        $data = [
            'specifiedDate' => '2015-01-01',
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setRemovalDate(new \DateTime());

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'version' => 1,
            'seedDate' => null
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);
        $licenceVehicle->setWarningLetterSeedDate(new DateTime());

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->with($licenceVehicle);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Vehicle updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(
            '2015-01-01 12:00:00',
            $licenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $licenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(100, $vehicle->getPlatedWeight());
        $this->assertNull($licenceVehicle->getWarningLetterSeedDate());
    }

    public function testHandleCommandUpdateSeedDates(): void
    {
        $data = [
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'version' => 1,
            'seedDate' => '2015-01-01',
            'sentDate' => '2016-01-01',
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);
        $licenceVehicle->setWarningLetterSeedDate(new DateTime());

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->with($licenceVehicle);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Vehicle updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(
            '2015-01-01 12:00:00',
            $licenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $licenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(100, $vehicle->getPlatedWeight());
        $this->assertEquals('2015-01-01', $licenceVehicle->getWarningLetterSeedDate()->format('Y-m-d'));
        $this->assertEquals('2016-01-01', $licenceVehicle->getWarningLetterSentDate()->format('Y-m-d'));
    }

    public function testHandleCommandUpdateRemoved(): void
    {
        $data = [
            'removalDate' => '2015-01-01',
            'version' => 1
        ];
        $command = Cmd::create($data);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);
        $licenceVehicle->setRemovalDate(new \DateTime());

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->with($licenceVehicle);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Vehicle updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-01-01', $licenceVehicle->getRemovalDate()->format('Y-m-d'));
    }
}

<?php

declare(strict_types=1);

/**
 * Update Psv Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LicenceVehicle;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\CommandHandler\LicenceVehicle\UpdatePsvLicenceVehicle as CommandHandler;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\LicenceVehicle\UpdatePsvLicenceVehicle as Cmd;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Psv Licence Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdatePsvLicenceVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleCommandWithRemovalDateExternal(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1
            ]
        );

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->setRemovalDate(new DateTime());

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Entity\User\Permission::SELFSERVE_USER, null)
            ->andReturn(true);

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithRemovalDateInternalApplication(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'application' => 123
            ]
        );

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->setRemovalDate(new DateTime());

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Entity\User\Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $this->expectException(ForbiddenException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithRemovalDateInternalLicenceWithoutRemovalDate(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'licence' => 123,
                'removalDate' => null
            ]
        );

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->setRemovalDate(new DateTime());

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Entity\User\Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithRemovalDateInternalLicenceWithRemovalDate(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'licence' => 123,
                'removalDate' => '2015-01-01'
            ]
        );

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->setRemovalDate(new DateTime());

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->once()
            ->with($licenceVehicle);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Entity\User\Permission::SELFSERVE_USER, null)
            ->andReturn(false);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Removal date updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-01-01', $licenceVehicle->getRemovalDate()->format('Y-m-d'));
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(
            [
                'id' => 111,
                'version' => 1,
                'specifiedDate' => '2015-01-01T12:00:00+01:00',
                'receivedDate' => '2015-01-01',
                'makeModel' => 'Foo',
                'application' => 123
            ]
        );

        /** @var Entity\Vehicle\Vehicle $vehicle */
        $vehicle = m::mock(Entity\Vehicle\Vehicle::class)->makePartial();

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($licenceVehicle)
            ->shouldReceive('save')
            ->once()
            ->with($licenceVehicle);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $data = [
            'id' => 123,
            'section' => 'vehiclesPsv'
        ];
        $result1 = new Result();
        $result1->addMessage('UpdateApplicationCompletion');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result1);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Updated Vehicle',
                'UpdateApplicationCompletion'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-01-01', $licenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $licenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );

        $this->assertEquals('Foo', $vehicle->getMakeModel());
    }
}

<?php

declare(strict_types=1);

/**
 * Create Psv Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreatePsvVehicle as CommandHandler;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command\Licence\CreatePsvVehicle as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Create Psv Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreatePsvVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Vehicle', Repository\Vehicle::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class)->makePartial();
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);

        parent::setUp();
    }

    public function testHandleCommandVrmExists(): void
    {
        $command = Cmd::create(
            [
                'licence' => 111,
                'vrm' => 'AA11AAA'
            ]
        );

        /** @var Entity\Vehicle\Vehicle $vehicle1 */
        $vehicle1 = m::mock(Entity\Vehicle\Vehicle::class)->makePartial();
        $vehicle1->setVrm('AA11AAA');

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle1);

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licence);

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandVrmDoesntExist(): void
    {
        $command = Cmd::create(
            [
                'licence' => 111,
                'vrm' => 'AA11AAA',
                'makeModel' => 'Foo',
                'receivedDate' => '2015-01-01',
                'specifiedDate' => '2015-01-01T12:00:00+01:00'
            ]
        );

        /** @var Entity\Vehicle\Vehicle $vehicle1 */
        $vehicle1 = m::mock(Entity\Vehicle\Vehicle::class)->makePartial();
        $vehicle1->setVrm('BB11BBB');

        /** @var Entity\Licence\LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(Entity\Licence\LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setVehicle($vehicle1);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle1);

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licence);

        $savedVehicle = null;

        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')
            ->with('AA11AAA')
            ->andReturn([])
            ->once()
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Entity\Vehicle\Vehicle::class))
            ->andReturnUsing(
                function (Entity\Vehicle\Vehicle $vehicle) use (&$savedVehicle) {
                    $savedVehicle = $vehicle;
                    $vehicle->setId(123);
                    $this->assertEquals('AA11AAA', $vehicle->getVrm());
                    $this->assertEquals('Foo', $vehicle->getMakeModel());
                }
            );

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->once()
            ->with(m::type(Entity\Licence\LicenceVehicle::class))
            ->andReturnUsing(
                function (Entity\Licence\LicenceVehicle $licenceVehicle) use ($licence, &$savedVehicle) {
                    $licenceVehicle->setId(321);
                    $this->assertEquals('2015-01-01', $licenceVehicle->getReceivedDate()->format('Y-m-d'));
                    $this->assertEquals(
                        '2015-01-01 12:00:00',
                        $licenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
                    );
                    $this->assertSame($savedVehicle, $licenceVehicle->getVehicle());
                    $this->assertSame($licence, $licenceVehicle->getLicence());
                }
            );

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoOtherVehicles(): void
    {
        $command = Cmd::create(
            [
                'licence' => 111,
                'vrm' => 'AA11AAA',
                'makeModel' => 'Foo',
            ]
        );

        $activeVehicles = new ArrayCollection();

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licence);

        $savedVehicle = null;

        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')
            ->with('AA11AAA')
            ->andReturn([])
            ->once()
            ->shouldReceive('save')
            ->once()
            ->with(m::type(Entity\Vehicle\Vehicle::class))
            ->andReturnUsing(
                function (Entity\Vehicle\Vehicle $vehicle) use (&$savedVehicle) {
                    $savedVehicle = $vehicle;
                    $vehicle->setId(123);
                    $this->assertEquals('AA11AAA', $vehicle->getVrm());
                    $this->assertEquals('Foo', $vehicle->getMakeModel());
                }
            );

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->once()
            ->with(m::type(Entity\Licence\LicenceVehicle::class))
            ->andReturnUsing(
                function (Entity\Licence\LicenceVehicle $licenceVehicle) use ($licence, &$savedVehicle) {
                    $licenceVehicle->setId(321);
                    $this->assertEquals(date('Y-m-d'), $licenceVehicle->getSpecifiedDate()->format('Y-m-d'));
                    $this->assertSame($savedVehicle, $licenceVehicle->getVehicle());
                    $this->assertSame($licence, $licenceVehicle->getLicence());
                }
            );

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandVehicleExistsOnOtherLicence(): void
    {
        $command = Cmd::create(
            [
                'licence' => 111,
                'vrm' => 'AA11AAA',
                'makeModel' => 'Foo',
            ]
        );

        $activeVehicles = new ArrayCollection();

        /** @var Entity\Licence\Licence $licence */
        $licence = m::mock(Entity\Licence\Licence::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(111)
            ->andReturn($licence);

        $mockVehicle = m::mock(Entity\Vehicle\Vehicle::class)
            ->shouldReceive('getId')->once()->andReturn(123)
            ->shouldReceive('setMakeModel')->with('Foo')->once()
            ->getMock();

        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')
            ->with('AA11AAA')
            ->andReturn([$mockVehicle])
            ->once();
        $this->repoMap['Vehicle']
            ->shouldReceive('save')
            ->with($mockVehicle)
            ->once();

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->once()
            ->with(m::type(Entity\Licence\LicenceVehicle::class))
            ->andReturnUsing(
                function (Entity\Licence\LicenceVehicle $licenceVehicle) use ($licence, &$mockVehicle) {
                    $licenceVehicle->setId(321);
                    $this->assertEquals(date('Y-m-d'), $licenceVehicle->getSpecifiedDate()->format('Y-m-d'));
                    $this->assertSame($mockVehicle, $licenceVehicle->getVehicle());
                    $this->assertSame($licence, $licenceVehicle->getLicence());
                }
            );

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Entity\User\Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->expectedLicenceCacheClear($licence);
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

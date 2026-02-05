<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\CreateGoodsVehicle as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\CreateGoodsVehicle;
use Dvsa\Olcs\Api\Domain\Exception\RequiresConfirmationException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\CreateGoodsVehicle::class)]
class CreateGoodsVehicleTest extends AbstractCommandHandlerTestCase
{
    public const LIC_ID = 9001;
    public const APP_ID = 8001;
    public const VRM = 'UNIT VRM';

    /** @var CreateGoodsVehicle  */
    protected $sut;

    /** @var Entity\Application\Application | m\MockInterface */
    private $mockApp;
    /** @var Entity\Vehicle\Vehicle | m\MockInterface */
    private $mockVehicle;
    /** @var Entity\Licence\Licence | m\MockInterface */
    private $mockLic;

    public function setUp(): void
    {
        $this->sut = new CreateGoodsVehicle();

        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);
        $this->mockRepo('Vehicle', Repository\Vehicle::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        $this->mockApp = m::mock(Entity\Application\Application::class)->makePartial();
        $this->mockApp->setId(self::APP_ID);

        $this->mockVehicle = m::mock(Entity\Vehicle\Vehicle::class)->makePartial();
        $this->mockVehicle->setVrm(self::VRM);

        $this->mockLic = m::mock(Entity\Licence\Licence::class)->makePartial();
        $this->mockLic->setId(self::LIC_ID);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];

        $this->references = [
            Entity\Application\Application::class => [
                self::APP_ID => $this->mockApp,
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandAlreadyExists(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => self::VRM,
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($this->mockVehicle);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle);

        $this->mockLic->shouldReceive('getActiveVehicles')->andReturn($activeVehicles);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with(self::VRM)->once()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')->with(self::LIC_ID)->andReturn($this->mockLic);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandVrmSection26(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => self::VRM,
            'applicationId' => null,
        ];
        $command = Cmd::create($data);

        $this->mockVehicle->setSection26(true);

        $this->repoMap['Licence']->shouldReceive('fetchById')->with(self::LIC_ID)->once()->andReturn($this->mockLic);
        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')->with(self::VRM)->once()->andReturn([$this->mockVehicle]);

        try {
            $this->sut->handleCommand($command);
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertSame(['vrm' => [Vehicle::ERROR_VRM_HAS_SECTION_26]], $e->getMessages());
        }
    }

    public function testHandleCommandRequiredConfirmationSelfserve(): void
    {
        $this->expectException(RequiresConfirmationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        /** @var Licence $otherLicence1 */
        $otherLicence1 = m::mock(Licence::class)->makePartial();
        $otherLicences = [$licence, $otherLicence1];

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->once()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        $this->repoMap['Licence']->shouldReceive('fetchByVrm')
            ->with('ABC123', true)
            ->andReturn($otherLicences);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRequiredConfirmationSelfserveIdentifyDuplicates(): void
    {
        $this->expectException(RequiresConfirmationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'identifyDuplicates' => true,
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->once()->andReturn([]);
        $this->repoMap['LicenceVehicle']->shouldReceive('fetchDuplicates')
            ->with($licence, 'ABC123', false)
            ->andReturn($licenceVehicles);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(false);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRequiredConfirmationInternal(): void
    {
        $this->expectException(RequiresConfirmationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        /** @var Application $application2 */
        $application2 = m::mock(Application::class)->makePartial();
        $application2->setId(self::LIC_ID);

        $applications2 = new ArrayCollection();
        $applications2->add($application2);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();
        $vehicle->setVrm('ABC456');

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        /** @var Licence $otherLicence1 */
        $otherLicence1 = m::mock(Licence::class)->makePartial();
        $otherLicence1->setLicNo('OB12345678');

        /** @var Licence $otherLicence2 */
        $otherLicence2 = m::mock(Licence::class)->makePartial();
        $otherLicence2->setApplications($applications2);

        $otherLicences = [$licence, $otherLicence1, $otherLicence2];

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->once()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        $this->repoMap['Licence']->shouldReceive('fetchByVrm')
            ->with('ABC123', true)
            ->andReturn($otherLicences);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandRequiredConfirmationInternalIdentifyDuplicates(): void
    {
        $this->expectException(RequiresConfirmationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'identifyDuplicates' => true,
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        /** @var Application $application2 */
        $application2 = m::mock(Application::class)->makePartial();
        $application2->setId(self::LIC_ID);

        $applications2 = new ArrayCollection();
        $applications2->add($application2);

        /** @var Vehicle $vehicle */
        $vehicle = m::mock(Vehicle::class)->makePartial();
        $vehicle->setVrm('ABC456');

        /** @var LicenceVehicle $licenceVehicle */
        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->setVehicle($vehicle);

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        /** @var Licence $otherLicence1 */
        $otherLicence1 = m::mock(Licence::class)->makePartial();
        $otherLicence1->setLicNo('OB12345678');

        /** @var Licence $otherLicence2 */
        $otherLicence2 = m::mock(Licence::class)->makePartial();
        $otherLicence2->setApplications($applications2);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->once()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        /** @var LicenceVehicle $licenceVehicle1 */
        $licenceVehicle1 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle1->setLicence($otherLicence1);

        /** @var LicenceVehicle $licenceVehicle2 */
        $licenceVehicle2 = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle2->setLicence($otherLicence2);

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle1);
        $licenceVehicles->add($licenceVehicle2);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchDuplicates')
            ->with($licence, 'ABC123', false)
            ->andReturn($licenceVehicles);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommand(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $otherLicences = [];

        $vehicle = new Vehicle();
        $vehicle->setId(123);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([$vehicle]);
        $this->repoMap['Vehicle']->shouldReceive('save')->with($vehicle)->once()->andReturn([$vehicle]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence)
            ->shouldReceive('fetchByVrm')
            ->with('ABC123', true)
            ->andReturn($otherLicences);

        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);
        $this->assertSame($vehicle, $savedLicenceVehicle->getVehicle());

        $this->assertSame($licence, $savedLicenceVehicle->getLicence());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(100, $vehicle->getPlatedWeight());
    }

    public function testHandleCommandIdentifyDuplicates(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'identifyDuplicates' => true,
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        /** @var Vehicle $savedVehicle */
        $savedVehicle = null;
        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['Vehicle']->shouldReceive('save')
            ->with(m::type(Vehicle::class))
            ->andReturnUsing(
                function (Vehicle $vehicle) use (&$savedVehicle) {
                    $vehicle->setId(123);
                    $savedVehicle = $vehicle;
                }
            );

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchDuplicates')
            ->with($licence, 'ABC123', false)
            ->andReturn([])
            ->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $savedVehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);

        $this->assertSame($savedVehicle, $savedLicenceVehicle->getVehicle());
        $this->assertEquals('ABC123', $savedVehicle->getVrm());
        $this->assertEquals(100, $savedVehicle->getPlatedWeight());

        $this->assertSame($licence, $savedLicenceVehicle->getLicence());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
    }

    public function testHandleCommandAlternative(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $otherLicences = [$licence];

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence)
            ->shouldReceive('fetchByVrm')
            ->with('ABC123', true)
            ->andReturn($otherLicences);

        /** @var Vehicle $savedVehicle */
        $savedVehicle = null;
        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['Vehicle']->shouldReceive('save')
            ->with(m::type(Vehicle::class))
            ->andReturnUsing(
                function (Vehicle $vehicle) use (&$savedVehicle) {
                    $vehicle->setId(123);
                    $savedVehicle = $vehicle;
                }
            );

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $savedVehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);

        $this->assertSame($savedVehicle, $savedLicenceVehicle->getVehicle());
        $this->assertEquals('ABC123', $savedVehicle->getVrm());
        $this->assertEquals(100, $savedVehicle->getPlatedWeight());

        $this->assertSame($licence, $savedLicenceVehicle->getLicence());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
    }

    public function testHandleCommandIdentifyDuplicatesAlternative(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'identifyDuplicates' => true,
            'confirm' => true,
            'applicationId' => null
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->andReturn($activeVehicles);

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([]);
        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence);

        /** @var Vehicle $savedVehicle */
        $savedVehicle = null;
        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['Vehicle']->shouldReceive('save')
            ->with(m::type(Vehicle::class))
            ->andReturnUsing(
                function (Vehicle $vehicle) use (&$savedVehicle) {
                    $vehicle->setId(123);
                    $savedVehicle = $vehicle;
                }
            );

        $licenceVehicle = m::mock(LicenceVehicle::class)->makePartial();
        $licenceVehicle->shouldReceive('updateDuplicateMark')->once();

        $licenceVehicles = new ArrayCollection();
        $licenceVehicles->add($licenceVehicle);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchDuplicates')
            ->with($licence, 'ABC123', false)
            ->andReturn($licenceVehicles)
            ->shouldReceive('save')
            ->with($licenceVehicle)
            ->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $savedVehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);

        $this->assertSame($savedVehicle, $savedLicenceVehicle->getVehicle());
        $this->assertEquals('ABC123', $savedVehicle->getVrm());
        $this->assertEquals(100, $savedVehicle->getPlatedWeight());

        $this->assertSame($licence, $savedLicenceVehicle->getLicence());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
    }

    public function testHandleCommandForApplication(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => self::APP_ID,
        ];
        $command = Cmd::create($data);

        $activeVehicle = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                ->shouldReceive('getVrm')
                ->andReturn('FOO')
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getApplication')
            ->andReturn(null)
            ->once()
            ->getMock();

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($activeVehicle);

        $this->mockLic->shouldReceive('getActiveVehicles')->with(false)->andReturn($activeVehicles)->once();

        $otherLicences = [];

        $vehicle = new Vehicle();
        $vehicle->setId(123);

        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([$vehicle])
            ->shouldReceive('save')->with($vehicle)->once()->andReturn([$vehicle]);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')->with(self::LIC_ID)->andReturn($this->mockLic)
            ->shouldReceive('fetchByVrm')->with('ABC123', true)->andReturn($otherLicences);

        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);
        $this->assertSame($vehicle, $savedLicenceVehicle->getVehicle());

        $this->assertSame($this->mockLic, $savedLicenceVehicle->getLicence());
        $this->assertSame($this->mockApp, $savedLicenceVehicle->getApplication());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(100, $vehicle->getPlatedWeight());
    }

    public function testHandleCommandForApplicationNoActivevehicles(): void
    {
        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => self::APP_ID,
        ];
        $command = Cmd::create($data);

        $activeVehicles = new ArrayCollection();

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles)
            ->once();

        $otherLicences = [];

        $vehicle = new Vehicle();
        $vehicle->setId(123);

        $this->repoMap['Vehicle']
            ->shouldReceive('fetchByVrm')->with('ABC123')->twice()->andReturn([$vehicle])
            ->shouldReceive('save')->with($vehicle)->once()->andReturn([$vehicle]);

        $this->repoMap['Licence']
            ->shouldReceive('fetchById')->with(self::LIC_ID)->andReturn($licence)
            ->shouldReceive('fetchByVrm')->with('ABC123', true)->andReturn($otherLicences);

        /** @var LicenceVehicle $savedLicenceVehicle */
        $savedLicenceVehicle = null;

        $this->repoMap['LicenceVehicle']->shouldReceive('save')
            ->with(m::type(LicenceVehicle::class))
            ->andReturnUsing(
                function (LicenceVehicle $licenceVehicle) use (&$savedLicenceVehicle) {
                    $licenceVehicle->setId(321);
                    $savedLicenceVehicle = $licenceVehicle;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'vehicle' => 123,
                'licenceVehicle' => 321
            ],
            'messages' => [
                'Vehicle created',
                'Licence Vehicle created'
            ],
            'flags' => [
                'vrm' => 'ABC123'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertInstanceOf(LicenceVehicle::class, $savedLicenceVehicle);
        $this->assertSame($vehicle, $savedLicenceVehicle->getVehicle());

        $this->assertSame($licence, $savedLicenceVehicle->getLicence());
        $this->assertSame($this->mockApp, $savedLicenceVehicle->getApplication());
        $this->assertEquals(
            '2015-01-01 12:00:00',
            $savedLicenceVehicle->getSpecifiedDate()->format('Y-m-d H:i:s')
        );
        $this->assertEquals('2015-02-02', $savedLicenceVehicle->getReceivedDate()->format('Y-m-d'));
        $this->assertEquals(100, $vehicle->getPlatedWeight());
    }

    public function testHandleCommandForApplicationWithException(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => self::VRM,
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => self::APP_ID,
        ];
        $command = Cmd::create($data);

        $licenceVehicle = m::mock()
            ->shouldReceive('getApplication')->andReturn($this->mockApp)->once()
            ->shouldReceive('getVehicle')->andReturn($this->mockVehicle)->once()
            ->getMock();

        $activeVehicles = new ArrayCollection();
        $activeVehicles->add($licenceVehicle);

        $this->mockLic->shouldReceive('getActiveVehicles')->with(false)->andReturn($activeVehicles)->once();

        $this->repoMap['Licence']->shouldReceive('fetchById')->with(self::LIC_ID)->andReturn($this->mockLic)->once();
        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')->with(self::VRM)->once()->andReturn([]);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandForApplicationWithExceptionAlternative(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'licence' => self::LIC_ID,
            'vrm' => 'ABC123',
            'platedWeight' => 100,
            'specifiedDate' => '2015-01-01T12:00:00+01:00',
            'receivedDate' => '2015-02-02',
            'applicationId' => 999
        ];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        $this->repoMap['Vehicle']->shouldReceive('fetchByVrm')
            ->with('ABC123')
            ->once()
            ->andReturn([]);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with(self::LIC_ID)
            ->andReturn($licence)
            ->once();

        $activeVehicles = new ArrayCollection();

        $licenceVehicle = m::mock()
            ->shouldReceive('getApplication')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(888)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('ABC123')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getSpecifiedDate')
            ->andReturn('01/01/2017')
            ->once()
            ->getMock();

        $activeVehicles->add($licenceVehicle);

        $licence->shouldReceive('getActiveVehicles')
            ->with(false)
            ->andReturn($activeVehicles)
            ->once();

        $this->sut->handleCommand($command);
    }
}

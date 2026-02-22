<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Vehicle;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\LicenceVehicle as LicenceVehicleRepo;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Vehicle\ProcessDuplicateVehicleRemoval;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\ProcessDuplicateVehicleRemoval as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Vehicle\RemoveDuplicateVehicle as RemoveDuplicateVehicleCmd;
use Dvsa\Olcs\Email\Domain\Command\SendEmail as SendEmailCmd;
use Dvsa\Olcs\Email\Service\TemplateRenderer;

/**
 * Process Duplicate Vehicle Removal Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ProcessDuplicateVehicleRemovalTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ProcessDuplicateVehicleRemoval();

        $this->mockRepo('LicenceVehicle', LicenceVehicleRepo::class);
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);

        $this->mockedSmServices = [
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testHandleCommandNoResults(): void
    {
        $command = Cmd::create([]);

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn([])
            ->once();

        $expected = [
            'id' => [],
            'messages' => [
                'Nothing to process'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommand(): void
    {
        $licVehicleId2 = 2;

        $command = Cmd::create([]);

        $mockLicence1 = m::mock(LicenceEntity::class);

        $mockLicence2 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO2')
            ->once()
            ->getMock();

        $licenceVehicle1 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                ->shouldReceive('getVrm')
                ->andReturn('VRM1')
                ->once()
                ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence1)
            ->once()
            ->shouldReceive('removeDuplicateMark')
            ->with(true)
            ->once()
            ->getMock();

        $licenceVehicle2 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence2)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId2)
            ->once()
            ->getMock();

        $licenceVehicles = [$licenceVehicle1, $licenceVehicle2];

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence1, 'VRM1', false)
            ->andReturnNull()
            ->once()
            ->shouldReceive('save')
            ->with($licenceVehicle1)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence2, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->getMock();

        $data = [
            'id' => $licVehicleId2
        ];

        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DUPLICATE_VEHICLE_EMAIL_LIST)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        $emailParams = [
            'removedVehicles' => [
                [
                    'vrm' => 'VRM2',
                    'licNo' => 'LICNO2',
                ]
            ]
        ];
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'email-duplicate-vehicles-removal',
            $emailParams,
            'default'
        );

        $data = [
            'to' => 'FOO',
            'locale' => 'en_GB',
            'subject' => 'email.duplicate-vehicles-removal.subject'
        ];

        $this->expectedSideEffect(SendEmailCmd::class, $data, new Result());

        $expected = [
            'id' => [],
            'messages' => [
                '2 succeeded',
                'Removed vehicle list successfully sent to FOO',
                '1 vehicle(s) removed',
                '1 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithRemoveVehicleException(): void
    {
        $licVehicleId = 2;

        $command = Cmd::create([]);

        $mockLicence = m::mock(LicenceEntity::class);

        $licenceVehicle = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId)
            ->once()
            ->getMock();

        $licenceVehicles = [$licenceVehicle];

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->getMock();

        $data = [
            'id' => $licVehicleId
        ];

        $this->expectedSideEffectThrowsException(RemoveDuplicateVehicleCmd::class, $data, new \Exception());

        $expected = [
            'id' => [],
            'messages' => [
                '2 failed: ',
                '0 vehicle(s) removed',
                '0 record(s) no longer duplicates',
                '1 failed record(s)'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoEmailAddress(): void
    {
        $licVehicleId = 2;

        $command = Cmd::create([]);

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO2')
            ->once()
            ->getMock();

        $licenceVehicle = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId)
            ->once()
            ->getMock();

        $licenceVehicles = [$licenceVehicle];

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->getMock();

        $data = [
            'id' => $licVehicleId
        ];

        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DUPLICATE_VEHICLE_EMAIL_LIST)
            ->andReturnNull()
            ->once()
            ->getMock();

        $expected = [
            'id' => [],
            'messages' => [
                '2 succeeded',
                '1 vehicle(s) removed',
                '0 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandSorting(): void
    {
        $licVehicleId1 = 1;
        $licVehicleId2 = 2;
        $licVehicleId3 = 3;

        $command = Cmd::create([]);

        $mockLicence1 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO1')
            ->once()
            ->getMock();

        $mockLicence2 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO2')
            ->once()
            ->getMock();

        $mockLicence3 = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO3')
            ->once()
            ->getMock();

        $licenceVehicle1 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM3')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence1)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId1)
            ->once()
            ->getMock();

        $licenceVehicle2 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence2)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId2)
            ->once()
            ->getMock();

        $licenceVehicle3 = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence3)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId3)
            ->once()
            ->getMock();

        $licenceVehicles = [$licenceVehicle1, $licenceVehicle2, $licenceVehicle3];

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence1, 'VRM3', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence2, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence3, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->getMock();

        $data = ['id' => $licVehicleId1];
        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $data = ['id' => $licVehicleId2];
        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $data = ['id' => $licVehicleId3];
        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DUPLICATE_VEHICLE_EMAIL_LIST)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        $emailParams = [
            'removedVehicles' => [
                ['vrm' => 'VRM2', 'licNo' => 'LICNO2'],
                ['vrm' => 'VRM2', 'licNo' => 'LICNO3'],
                ['vrm' => 'VRM3', 'licNo' => 'LICNO1'],
            ]
        ];
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'email-duplicate-vehicles-removal',
            $emailParams,
            'default'
        )->once();

        $data = [
            'to' => 'FOO',
            'locale' => 'en_GB',
            'subject' => 'email.duplicate-vehicles-removal.subject'
        ];

        $this->expectedSideEffect(SendEmailCmd::class, $data, new Result());

        $expected = [
            'id' => [],
            'messages' => [
                '1 succeeded',
                '2 succeeded',
                '3 succeeded',
                'Removed vehicle list successfully sent to FOO',
                '3 vehicle(s) removed',
                '0 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithSendEmailException(): void
    {
        $licVehicleId = 2;

        $command = Cmd::create([]);

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('getLicNo')
            ->andReturn('LICNO2')
            ->once()
            ->getMock();

        $licenceVehicle = m::mock()
            ->shouldReceive('getVehicle')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getVrm')
                    ->andReturn('VRM2')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->shouldReceive('getId')
            ->andReturn($licVehicleId)
            ->once()
            ->getMock();

        $licenceVehicles = [$licenceVehicle];

        $this->repoMap['LicenceVehicle']
            ->shouldReceive('fetchForRemoval')
            ->andReturn($licenceVehicles)
            ->once()
            ->shouldReceive('fetchDuplicates')
            ->with($mockLicence, 'VRM2', false)
            ->andReturn('DUPLICATES')
            ->once()
            ->getMock();

        $data = ['id' => $licVehicleId];
        $this->expectedSideEffect(RemoveDuplicateVehicleCmd::class, $data, new Result());

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchValue')
            ->with(SystemParameter::DUPLICATE_VEHICLE_EMAIL_LIST)
            ->andReturn('FOO')
            ->once()
            ->getMock();

        $emailParams = [
            'removedVehicles' => [
                [
                    'vrm' => 'VRM2',
                    'licNo' => 'LICNO2',
                ]
            ]
        ];
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->with(
            m::type(\Dvsa\Olcs\Email\Data\Message::class),
            'email-duplicate-vehicles-removal',
            $emailParams,
            'default'
        )
        ->andThrow(new \Exception());

        $expected = [
            'id' => [],
            'messages' => [
                '2 succeeded',
                'Error sending removed vehicle list to FOO',
                '1 vehicle(s) removed',
                '0 record(s) no longer duplicates',
                '0 failed record(s)'
            ]
        ];

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }
}

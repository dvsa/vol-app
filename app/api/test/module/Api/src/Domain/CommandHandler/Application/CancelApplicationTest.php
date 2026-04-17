<?php

declare(strict_types=1);

/**
 * Cancel Application Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CancelApplication;
use Dvsa\Olcs\Transfer\Command\Application\CancelApplication as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Cancel Application Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CancelApplicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelApplication();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ApplicationEntity::APPLICATION_STATUS_CANCELLED,
            LicenceEntity::LICENCE_STATUS_CANCELLED
        ];

        parent::initReferences();
    }

    public function testHandleCommandForVariation(): void
    {
        $applicationId = 834;
        $command = Cmd::create(['id' => $applicationId]);

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('setStatus')
            ->with($this->refData[ApplicationEntity::APPLICATION_STATUS_CANCELLED])
            ->once()
            ->shouldReceive('getId')
            ->andReturn($applicationId)
            ->shouldReceive('getIsVariation')
            ->andReturn(true)
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($mockApplication)
            ->shouldReceive('save')
            ->with($mockApplication)
            ->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 834],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => [
                'Application cancelled'
            ],
            'id' => [
                'application' => $applicationId
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForApplication(): void
    {
        $applicationId = 834;
        $command = Cmd::create(['id' => $applicationId]);

        $mockLicence = m::mock()
            ->shouldReceive('setStatus')
            ->with($this->refData[LicenceEntity::LICENCE_STATUS_CANCELLED])
            ->once()
            ->getMock();

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('setStatus')
            ->with($this->refData[ApplicationEntity::APPLICATION_STATUS_CANCELLED])
            ->once()
            ->shouldReceive('getId')
            ->andReturn($applicationId)
            ->shouldReceive('getIsVariation')
            ->andReturn(false)
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn($mockLicence)
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($mockApplication)
            ->shouldReceive('save')
            ->with($mockApplication)
            ->once();

        $this->repoMap['Licence']
            ->shouldReceive('save')
            ->with($mockLicence)
            ->once();

        $this->expectedSideEffect(
            \Dvsa\Olcs\Api\Domain\Command\Application\CancelOutstandingFees::class,
            ['id' => 834],
            new \Dvsa\Olcs\Api\Domain\Command\Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'messages' => [
                'Application cancelled'
            ],
            'id' => [
                'application' => $applicationId
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}

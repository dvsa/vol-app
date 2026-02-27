<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\CreateSmallVehicleCondition as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateSmallVehicleCondition as Command;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as CreateConditionUndertakingCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;

/**
 * Create small vehicle condition
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CreateSmallVehicleConditionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $applicationId = 1;
        $licenceId = 2;

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('getPsvWhichVehicleSizes')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(ApplicationEntity::PSV_VEHICLE_SIZE_SMALL)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceId)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getId')
            ->andReturn($applicationId)
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($mockApplication);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchSmallVehilceUndertakings')
            ->with($licenceId)
            ->andReturn([])
            ->once();

        $data = [
            'attachedTo' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE,
            'type' => ConditionUndertakingEntity::TYPE_UNDERTAKING,
            'notes' => CommandHandler::SMALL_VEHICLE_UNERRTAKINGS_NOTES,
            'application' => $applicationId
        ];

        $this->expectedSideEffect(CreateConditionUndertakingCmd::class, $data, new Result());
        $command = Command::create(['applicationId' => $applicationId]);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandLargeVehicles(): void
    {
        $applicationId = 1;

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('getPsvWhichVehicleSizes')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(ApplicationEntity::PSV_VEHICLE_SIZE_MEDIUM_LARGE)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($mockApplication);

        $command = Command::create(['applicationId' => $applicationId]);
        $this->sut->handleCommand($command);
    }

    public function testHandleCommandConditionExists(): void
    {
        $applicationId = 1;
        $licenceId = 2;

        $mockApplication = m::mock(ApplicationEntity::class)
            ->shouldReceive('getPsvWhichVehicleSizes')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(ApplicationEntity::PSV_VEHICLE_SIZE_SMALL)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn($licenceId)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $this->repoMap['Application']
            ->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($mockApplication);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchSmallVehilceUndertakings')
            ->with($licenceId)
            ->andReturn(['cond1'])
            ->once();

        $command = Command::create(['applicationId' => $applicationId]);

        $this->sut->handleCommand($command);
    }
}

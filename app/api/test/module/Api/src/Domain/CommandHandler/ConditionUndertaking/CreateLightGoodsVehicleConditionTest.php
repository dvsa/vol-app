<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ConditionUndertaking;

use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\ConditionUndertaking\CreateLightGoodsVehicleCondition as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\ConditionUndertaking\CreateLightGoodsVehicleCondition as Command;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\ConditionUndertaking\Create as CreateConditionUndertakingCmd;
use Mockery as m;

/**
 * Create light goods vehicle condition test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class CreateLightGoodsVehicleConditionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $applicationId = 42;
        $licenceId = 62;

        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('getVehicleType->getId')
            ->withNoArgs()
            ->andReturn(RefData::APP_VEHICLE_TYPE_LGV);
        $application->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($application);

        $this->repoMap['ConditionUndertaking']->shouldReceive('hasLightGoodsVehicleUndertakings')
            ->with($licenceId)
            ->andReturnFalse();

        $data = [
            'attachedTo' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE,
            'conditionCategory' => ConditionUndertakingEntity::CATEGORY_OTHER,
            'type' => ConditionUndertakingEntity::TYPE_UNDERTAKING,
            'notes' => ConditionUndertakingEntity::LIGHT_GOODS_VEHICLE_UNDERTAKINGS,
            'application' => $applicationId
        ];

        $this->expectedSideEffect(CreateConditionUndertakingCmd::class, $data, new Result());
        $command = Command::create(['applicationId' => $applicationId]);

        $this->sut->handleCommand($command);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandNoUndertakingRequired')]
    public function testHandleCommandNoUndertakingRequired(mixed $vehicleTypeId, mixed $hasLightGoodsVehicleUndertakings): void
    {
        $applicationId = 44;
        $licenceId = 31;

        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('getVehicleType->getId')
            ->withNoArgs()
            ->andReturn($vehicleTypeId);

        $application->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($application);

        $this->repoMap['ConditionUndertaking']->shouldReceive('hasLightGoodsVehicleUndertakings')
            ->with($licenceId)
            ->andReturn($hasLightGoodsVehicleUndertakings);

        $command = Command::create(['applicationId' => $applicationId]);

        $this->sut->handleCommand($command);
    }

    public static function dpHandleCommandNoUndertakingRequired(): array
    {
        return [
            [RefData::APP_VEHICLE_TYPE_PSV, true],
            [RefData::APP_VEHICLE_TYPE_HGV, true],
            [RefData::APP_VEHICLE_TYPE_MIXED, true],
            [RefData::APP_VEHICLE_TYPE_LGV, true],
            [RefData::APP_VEHICLE_TYPE_PSV, false],
            [RefData::APP_VEHICLE_TYPE_HGV, false],
            [RefData::APP_VEHICLE_TYPE_MIXED, false],
        ];
    }
}

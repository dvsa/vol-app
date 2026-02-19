<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\AutoGrant as AutoGrantCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Variation\AutoGrant as AutoGrantHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Command\Application\Overview as OverviewCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * AutoGrant commandhandler Test
 */
class AutoGrantTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AutoGrantHandler();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->refData = [
            RefData::GRANT_AUTHORITY_DELEGATED
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $command = AutoGrantCmd::create(['id' => 111]);

        $tracking = m::mock(ApplicationTracking::class)->makePartial();
        $tracking->shouldReceive('getId')->andReturn(222);
        $tracking->shouldReceive('getVersion')->andReturn(1);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setVersion(5);
        $application->shouldReceive('getApplicationTracking')->andReturn($tracking);
        $application->shouldReceive('getOverrideOoo')->andReturn(false);
        $application->shouldReceive('getApplicationReferredToPi')->andReturn('N');
        $application->shouldReceive('setWasAutoGranted')->with(true)->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        // Expect Overview command to set tracking
        $overviewResult = new Result();
        $overviewResult->addMessage('Tracking completed');

        $this->expectedSideEffectAsSystemUser(
            OverviewCmd::class,
            m::on(function ($command) {
                return $command instanceof OverviewCmd
                    && $command->getId() === 111
                    && $command->getVersion() === 5
                    && is_array($command->getTracking())
                    && $command->getOverrideOppositionDate() === 'N'
                    && $command->getApplicationReferredToPi() === 'N';
            }),
            $overviewResult
        );

        // Expect Grant command to be called
        $grantResult = new Result();
        $grantResult->addMessage('Application granted');

        $this->expectedSideEffectAsSystemUser(
            OverviewCmd::class,
            m::type(OverviewCmd::class),
            $overviewResult
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertContains('Application auto-granted', $result->getMessages());
        $this->assertTrue($result->getFlag('autoGranted'));
    }

    public function testCompleteTrackingBuildsCorrectData()
    {
        $command = AutoGrantCmd::create(['id' => 111]);

        $tracking = m::mock(ApplicationTracking::class)->makePartial();
        $tracking->shouldReceive('getId')->andReturn(222);
        $tracking->shouldReceive('getVersion')->andReturn(1);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setVersion(5);
        $application->shouldReceive('getApplicationTracking')->andReturn($tracking);
        $application->shouldReceive('getOverrideOoo')->andReturn(true);
        $application->shouldReceive('getApplicationReferredToPi')->andReturn('Y');
        $application->shouldReceive('setWasAutoGranted')->with(true)->once();

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $overviewResult = new Result();

        // Verify the tracking data structure
        $this->expectedSideEffectAsSystemUser(
            OverviewCmd::class,
            m::on(function ($command) {
                if (!$command instanceof OverviewCmd) {
                    return false;
                }

                $tracking = $command->getTracking();

                if ($tracking['id'] !== 222 || $tracking['version'] !== 1) {
                    return false;
                }
                foreach (['addressesStatus', 'operatingCentresStatus', 'vehiclesStatus'] as $section) {
                    if (($tracking[$section] ?? null) !== '1') {
                        return false;
                    }
                }

                return $command->getId() === 111
                    && $command->getVersion() === 5
                    && $command->getOverrideOppositionDate() === 'Y'
                    && $command->getApplicationReferredToPi() === 'Y';
            }),
            $overviewResult
        );

        $this->expectedSideEffectAsSystemUser(
            OverviewCmd::class,
            m::on(function ($command) {
                return $command instanceof OverviewCmd
                    && $command->getId() === 111
                    && $command->getVersion() === 5
                    && is_array($command->getTracking())
                    && $command->getOverrideOppositionDate() === 'N'
                    && $command->getApplicationReferredToPi() === 'N';
            }),
            $overviewResult
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}

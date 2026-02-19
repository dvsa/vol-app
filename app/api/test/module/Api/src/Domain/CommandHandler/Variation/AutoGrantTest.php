<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Variation;

use Dvsa\Olcs\Api\Domain\Command\Application\Grant\AutoGrant as AutoGrantCmd;
use Dvsa\Olcs\Transfer\Command\Variation\Grant as VariationGrantCmd;
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
            RefData::GRANT_AUTHORITY_DELEGATED => m::mock(RefData::class)->makePartial()
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

        $refData = $this->refData;

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
            [
                'id' => 111,
                'version' => 5,
                'tracking' => [
                    'id' => 222,
                    'version' => 1,
                    'addressesStatus' => '1',
                    'businessDetailsStatus' => '1',
                    'businessTypeStatus' => '1',
                    'communityLicencesStatus' => '1',
                    'conditionsUndertakingsStatus' => '1',
                    'operatingCentresStatus' => '1',
                    'peopleStatus' => '1',
                    'safetyStatus' => '1',
                    'transportManagersStatus' => '1',
                    'typeOfLicenceStatus' => '1',
                    'declarationsInternalStatus' => '1',
                    'vehiclesStatus' => '1'
                ],
                'overrideOppositionDate' => 'N',
                'applicationReferredToPi' => 'N'
            ],
            $overviewResult
        );

        // Expect Grant command to be called
        $grantResult = new Result();
        $grantResult->addMessage('Application granted');

        $this->expectedSideEffectAsSystemUser(
            VariationGrantCmd::class,
            [
                'id' => 111,
                'grantAuthority' => $refData[RefData::GRANT_AUTHORITY_DELEGATED]
            ],
            $grantResult
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
        $refData = $this->refData;

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application)
            ->shouldReceive('save')
            ->once()
            ->with($application);

        $overviewResult = new Result();

        // Verify the tracking data structure with override values
        $this->expectedSideEffectAsSystemUser(
            OverviewCmd::class,
            [
                'id' => 111,
                'version' => 5,
                'tracking' => [
                    'id' => 222,
                    'version' => 1,
                    'addressesStatus' => '1',
                    'businessDetailsStatus' => '1',
                    'businessTypeStatus' => '1',
                    'communityLicencesStatus' => '1',
                    'conditionsUndertakingsStatus' => '1',
                    'operatingCentresStatus' => '1',
                    'peopleStatus' => '1',
                    'safetyStatus' => '1',
                    'transportManagersStatus' => '1',
                    'typeOfLicenceStatus' => '1',
                    'declarationsInternalStatus' => '1',
                    'vehiclesStatus' => '1'
                ],
                'overrideOppositionDate' => 'Y',
                'applicationReferredToPi' => 'Y'
            ],
            $overviewResult
        );

        $grantResult = new Result();
        $this->expectedSideEffectAsSystemUser(
            VariationGrantCmd::class,
            [
                'id' => 111,
                'grantAuthority' => $refData[RefData::GRANT_AUTHORITY_DELEGATED]
            ],
            $grantResult
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}

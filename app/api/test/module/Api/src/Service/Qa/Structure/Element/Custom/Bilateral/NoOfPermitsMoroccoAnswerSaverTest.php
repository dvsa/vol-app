<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\NoOfPermitsConditionalUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsMoroccoAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsMoroccoAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsMoroccoAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $defaultBilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => null,
        ];

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getDefaultBilateralRequired')
            ->withNoArgs()
            ->andReturn($defaultBilateralRequired);

        $applicationStep = m::mock(ApplicationStep::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);

        $noOfPermitsConditionalUpdater = m::mock(NoOfPermitsConditionalUpdater::class);
        $oldPermitsRequired = 12;
        $newPermitsRequired = 14;

        $bilateralRequired = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $oldPermitsRequired,
        ];

        $irhpPermitApplication->shouldReceive('getBilateralRequired')
            ->withNoArgs()
            ->andReturn($bilateralRequired);

        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData)
            ->andReturn($newPermitsRequired);

        $expectedUpdatedAnswers = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => null,
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => $newPermitsRequired,
        ];

        $noOfPermitsConditionalUpdater->shouldReceive('update')
            ->with($irhpPermitApplication, $expectedUpdatedAnswers)
            ->once();

        $noOfPermitsMoroccoAnswerSaver = new NoOfPermitsMoroccoAnswerSaver(
            $genericAnswerFetcher,
            $noOfPermitsConditionalUpdater
        );

        $noOfPermitsMoroccoAnswerSaver->save($qaContext, $postData);
    }
}

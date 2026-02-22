<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\ConditionalFeeUpdater;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\FieldNames;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerSaver;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $euro5Answer = '5';
        $euro6Answer = '27';
        $oldTotal = 13;

        $postData = [
            'fieldset68' => [
                'requiredEuro5' => '5',
                'requiredEuro6' => '27'
            ]
        ];

        $applicationStep = m::mock(ApplicationStepEntity::class);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('getTotalEmissionsCategoryPermitsRequired')
            ->once()
            ->andReturn($oldTotal)
            ->globally()
            ->ordered();
        $irhpPermitApplication->shouldReceive('updateEmissionsCategoryPermitsRequired')
            ->with($euro5Answer, $euro6Answer)
            ->once()
            ->globally()
            ->ordered();

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplication);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);

        $conditionalFeeUpdater = m::mock(ConditionalFeeUpdater::class);
        $conditionalFeeUpdater->shouldReceive('updateFees')
            ->with($irhpApplication, $oldTotal)
            ->once();

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $noOfPermitsAnswerFetcher = m::mock(NoOfPermitsAnswerFetcher::class);
        $noOfPermitsAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData, FieldNames::REQUIRED_EURO5)
            ->andReturn($euro5Answer);
        $noOfPermitsAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData, FieldNames::REQUIRED_EURO6)
            ->andReturn($euro6Answer);

        $noOfPermitsAnswerSaver = new NoOfPermitsAnswerSaver(
            $irhpPermitApplicationRepo,
            $noOfPermitsAnswerFetcher,
            $conditionalFeeUpdater
        );

        $noOfPermitsAnswerSaver->save($qaContext, $postData);
    }
}

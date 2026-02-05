<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\IntJourneysAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IntJourneysAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IntJourneysAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'fieldset68' => [
                'qaElement' => '5'
            ]
        ];

        $answer = 'int_journeys_refdata_key';

        $answerRefData = m::mock(RefData::class);

        $applicationStep = m::mock(ApplicationStepEntity::class);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('updateInternationalJourneys')
            ->with($answerRefData)
            ->once()
            ->globally()
            ->ordered();

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);
        $qaContext->shouldReceive('getQaEntity')
            ->andReturn($irhpApplication);

        $irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $irhpApplicationRepo->shouldReceive('getRefdataReference')
            ->with($answer)
            ->andReturn($answerRefData);
        $irhpApplicationRepo->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);
        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData)
            ->andReturn($answer);

        $intJourneysAnswerSaver = new IntJourneysAnswerSaver($irhpApplicationRepo, $genericAnswerFetcher);
        $intJourneysAnswerSaver->save($qaContext, $postData);
    }
}

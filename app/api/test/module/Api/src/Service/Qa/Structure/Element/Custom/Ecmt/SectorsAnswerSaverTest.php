<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\Sectors as SectorsEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\SectorsAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SectorsAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SectorsAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $answer = 8;

        $postData = [
            'fieldset68' => [
                'qaElement' => $answer
            ]
        ];

        $answerSectors = m::mock(SectorsEntity::class);

        $applicationStep = m::mock(ApplicationStepEntity::class);

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('updateSectors')
            ->with($answerSectors)
            ->once()
            ->globally()
            ->ordered();

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);

        $irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $irhpApplicationRepo->shouldReceive('getReference')
            ->with(SectorsEntity::class, $answer)
            ->andReturn($answerSectors);
        $irhpApplicationRepo->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);
        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData)
            ->andReturn($answer);

        $sectorsAnswerSaver = new SectorsAnswerSaver($irhpApplicationRepo, $genericAnswerFetcher);
        $sectorsAnswerSaver->save($qaContext, $postData);
    }
}

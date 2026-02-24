<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\AnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\FeeCreator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\NoOfPermitsAnswerSaver;
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
        $permitsRequired = 48;

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);

        $postData = [
            'fields123' => [
                'qaElement' => '48'
            ]
        ];

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStepEntity);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplicationEntity);

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);
        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStepEntity, $postData)
            ->andReturn($permitsRequired);

        $answerWriter = m::mock(AnswerWriter::class);
        $answerWriter->shouldReceive('write')
            ->with($irhpApplicationEntity, $permitsRequired)
            ->once();

        $feeCreator = m::mock(FeeCreator::class);
        $feeCreator->shouldReceive('create')
            ->with($irhpApplicationEntity, $permitsRequired)
            ->once();

        $noOfPermitsAnswerSaver = new NoOfPermitsAnswerSaver(
            $genericAnswerFetcher,
            $answerWriter,
            $feeCreator
        );

        $noOfPermitsAnswerSaver->save($qaContext, $postData);
    }
}

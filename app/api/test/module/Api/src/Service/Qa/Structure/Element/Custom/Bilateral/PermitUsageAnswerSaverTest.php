<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\Common\PermitUsageUpdater;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\PermitUsageAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageAnswerSaverTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $postData = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $answer = 'journey_multiple';

        $applicationStep = m::mock(ApplicationStep::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);
        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData)
            ->andReturn($answer);

        $permitUsageUpdater = m::mock(PermitUsageUpdater::class);
        $permitUsageUpdater->shouldReceive('update')
            ->with($qaContext, $answer)
            ->once();

        $permitUsageAnswerSaver = new PermitUsageAnswerSaver(
            $genericAnswerFetcher,
            $permitUsageUpdater
        );

        $permitUsageAnswerSaver->save($qaContext, $postData);
    }
}

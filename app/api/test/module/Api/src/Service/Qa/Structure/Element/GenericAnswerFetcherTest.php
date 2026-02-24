<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerFetcherTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerFetcherTest extends MockeryTestCase
{
    public function testFetch(): void
    {
        $postData = [
            'fields456' => [
                'qaElement' => '123'
            ]
        ];

        $answer = '43';

        $applicationStep = m::mock(ApplicationStepEntity::class);

        $namedAnswerFetcher = m::mock(NamedAnswerFetcher::class);
        $namedAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $postData, 'qaElement')
            ->andReturn($answer);

        $genericAnswerFetcher = new GenericAnswerFetcher($namedAnswerFetcher);

        $this->assertEquals(
            $answer,
            $genericAnswerFetcher->fetch($applicationStep, $postData)
        );
    }
}

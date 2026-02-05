<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NamedAnswerFetcherTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NamedAnswerFetcherTest extends MockeryTestCase
{
    private $fieldsetName;

    private $applicationStepEntity;

    private $namedAnswerFetcher;

    public function setUp(): void
    {
        $this->fieldsetName = 'fields123';

        $this->applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $this->applicationStepEntity->shouldReceive('getFieldsetName')
            ->andReturn($this->fieldsetName);

        $this->namedAnswerFetcher = new NamedAnswerFetcher();
    }

    public function testFetch(): void
    {
        $elementName = 'elementName';
        $answerValue = '123';

        $postData = [
            'qa' => [
                $this->fieldsetName => [
                    $elementName => $answerValue
                ]
            ]
        ];

        $this->assertEquals(
            $answerValue,
            $this->namedAnswerFetcher->fetch($this->applicationStepEntity, $postData, $elementName)
        );
    }

    public function testFetchExceptionWhenNoData(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(NamedAnswerFetcher::ERR_NO_ANSWER);

        $postData = [
            'qa' => [
                'fields456' => [
                    'qaElement' => '123'
                ]
            ]
        ];

        $this->namedAnswerFetcher->fetch($this->applicationStepEntity, $postData, 'field');
    }
}

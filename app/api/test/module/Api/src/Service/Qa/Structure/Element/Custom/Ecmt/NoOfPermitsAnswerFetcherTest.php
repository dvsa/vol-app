<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerFetcher;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\NamedAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerFetcherTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerFetcherTest extends MockeryTestCase
{
    private $applicationStep;

    private $postData;

    private $elementName;

    private $namedAnswerFetcher;

    private $noOfPermitsAnswerFetcher;

    public function setUp(): void
    {
        $this->applicationStep = m::mock(ApplicationStepEntity::class);

        $this->postData = [
            'fieldset68' => [
                'requiredEuro5' => '5',
                'requiredEuro6' => '27'
            ]
        ];

        $this->elementName = 'requiredEuro5';

        $this->namedAnswerFetcher = m::mock(NamedAnswerFetcher::class);

        $this->noOfPermitsAnswerFetcher = new NoOfPermitsAnswerFetcher($this->namedAnswerFetcher);
    }

    public function testFetchValuePresent(): void
    {
        $fetchedAnswer = '40';
        $expectedAnswer = '40';

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, $this->elementName)
            ->andReturn($fetchedAnswer);

        $this->assertEquals(
            $expectedAnswer,
            $this->noOfPermitsAnswerFetcher->fetch($this->applicationStep, $this->postData, $this->elementName)
        );
    }

    public function testFetchConvertEmptyToZero(): void
    {
        $fetchedAnswer = '';
        $expectedAnswer = '0';

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, $this->elementName)
            ->andReturn($fetchedAnswer);

        $this->assertEquals(
            $expectedAnswer,
            $this->noOfPermitsAnswerFetcher->fetch($this->applicationStep, $this->postData, $this->elementName)
        );
    }

    public function testFetchConvertMissingToZero(): void
    {
        $expectedAnswer = '0';

        $this->namedAnswerFetcher->shouldReceive('fetch')
            ->with($this->applicationStep, $this->postData, $this->elementName)
            ->andThrow(new NotFoundException());

        $this->assertEquals(
            $expectedAnswer,
            $this->noOfPermitsAnswerFetcher->fetch($this->applicationStep, $this->postData, $this->elementName)
        );
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Date;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\Date;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\DateFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\DateGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $answerValue = '2020-04-28';

        $date = m::mock(Date::class);

        $dateFactory = m::mock(DateFactory::class);
        $dateFactory->shouldReceive('create')
            ->with($answerValue)
            ->andReturn($date);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $dateGenerator = new DateGenerator($dateFactory);

        $this->assertSame(
            $date,
            $dateGenerator->generate($elementGeneratorContext)
        );
    }
}

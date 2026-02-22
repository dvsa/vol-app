<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Common;

use DateInterval;
use DateTime;
use Dvsa\Olcs\Api\Service\Common\CurrentDateTimeFactory;
use Dvsa\Olcs\Api\Service\Qa\Common\DateIntervalFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThresholdGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\Date as DateElement;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\DateGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateWithThresholdGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateWithThresholdGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);

        $dateIntervalString = 'P60D';
        $dateInterval = m::mock(DateInterval::class);

        $dateIntervalFactory = m::mock(DateIntervalFactory::class);
        $dateIntervalFactory->shouldReceive('create')
            ->with($dateIntervalString)
            ->once()
            ->andReturn($dateInterval);

        $currentDateTime = m::mock(DateTime::class);
        $currentDateTime->shouldReceive('add')
            ->with($dateInterval)
            ->once()
            ->globally()
            ->ordered();

        $currentDateTimeFactory = m::mock(CurrentDateTimeFactory::class);
        $currentDateTimeFactory->shouldReceive('create')
            ->withNoArgs()
            ->once()
            ->andReturn($currentDateTime);

        $dateElement = m::mock(DateElement::class);

        $dateGenerator = m::mock(DateGenerator::class);
        $dateGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext)
            ->once()
            ->andReturn($dateElement);

        $dateWithThreshold = m::mock(DateWithThreshold::class);

        $dateWithThresholdFactory = m::mock(DateWithThresholdFactory::class);
        $dateWithThresholdFactory->shouldReceive('create')
            ->with($currentDateTime, $dateElement)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($dateWithThreshold);

        $dateWithThresholdGenerator = new DateWithThresholdGenerator(
            $dateWithThresholdFactory,
            $currentDateTimeFactory,
            $dateIntervalFactory,
            $dateGenerator
        );

        $this->assertSame(
            $dateWithThreshold,
            $dateWithThresholdGenerator->generate($elementGeneratorContext, $dateIntervalString)
        );
    }
}

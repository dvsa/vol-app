<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Common;

use DateTime;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Common\DateWithThreshold;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Date\Date as DateElement;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateWithThresholdTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateWithThresholdTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $dateRepresentation = [
            'value' => '2020-05-03',
        ];

        $formattedDateThresholdDateTime = '2020-03-02';

        $date = m::mock(DateElement::class);
        $date->shouldReceive('getRepresentation')
            ->andReturn($dateRepresentation);

        $dateThresholdDateTime = m::mock(DateTime::class);
        $dateThresholdDateTime->shouldReceive('format')
            ->with('Y-m-d')
            ->andReturn($formattedDateThresholdDateTime);

        $dateWithThreshold = new DateWithThreshold($dateThresholdDateTime, $date);

        $expectedRepresentation = [
            'dateThreshold' => $formattedDateThresholdDateTime,
            'date' => $dateRepresentation,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $dateWithThreshold->getRepresentation()
        );
    }
}

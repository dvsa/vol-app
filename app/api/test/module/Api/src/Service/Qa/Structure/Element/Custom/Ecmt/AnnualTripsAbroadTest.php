<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\AnnualTripsAbroad;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Text;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnnualTripsAbroadTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnnualTripsAbroadTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTrueFalse
     */
    public function testGetRepresentation($showNiWarning)
    {
        $intensityWarningThreshold = 47;

        $textRepresentation = [
            'textKey1' => 'textValue1',
            'textKey2' => 'textValue2',
        ];

        $text = m::mock(Text::class);
        $text->shouldReceive('getRepresentation')
            ->andReturn($textRepresentation);

        $annualTripsAbroad = new AnnualTripsAbroad($intensityWarningThreshold, $showNiWarning, $text);

        $expectedRepresentation = [
            'intensityWarningThreshold' => $intensityWarningThreshold,
            'showNiWarning' => $showNiWarning,
            'text' => $textRepresentation,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $annualTripsAbroad->getRepresentation()
        );
    }

    public function dpTrueFalse()
    {
        return [
            [true],
            [false]
        ];
    }
}

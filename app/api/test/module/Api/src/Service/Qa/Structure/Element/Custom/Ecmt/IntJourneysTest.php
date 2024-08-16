<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\IntJourneys;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IntJourneysTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IntJourneysTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTrueFalse
     */
    public function testGetRepresentation($showNiWarning)
    {
        $radioRepresentation = [
            'radioKey1' => 'radioValue1',
            'radioKey2' => 'radioValue2',
        ];

        $radio = m::mock(Radio::class);
        $radio->shouldReceive('getRepresentation')
            ->andReturn($radioRepresentation);

        $intJourneys = new IntJourneys($showNiWarning, $radio);

        $expectedRepresentation = [
            'showNiWarning' => $showNiWarning,
            'radio' => $radioRepresentation,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $intJourneys->getRepresentation()
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

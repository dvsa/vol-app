<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\ThirdCountry;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ThirdCountryTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ThirdCountryTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetRepresentation
     */
    public function testGetRepresentation($yesNo)
    {
        $thirdCountry = new ThirdCountry($yesNo);

        $expectedRepresentation = ['yesNo' => $yesNo];

        $this->assertEquals(
            $expectedRepresentation,
            $thirdCountry->getRepresentation()
        );
    }

    public function dpGetRepresentation()
    {
        return [
            ['Y'],
            [null]
        ];
    }
}

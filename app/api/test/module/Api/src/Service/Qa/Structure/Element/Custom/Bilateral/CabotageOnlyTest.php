<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\CabotageOnly;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CabotageOnlyTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CabotageOnlyTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetRepresentation
     */
    public function testGetRepresentation($yesNo)
    {
        $countryName = 'Germany';

        $cabotageOnly = new CabotageOnly($yesNo, $countryName);

        $expectedRepresentation = [
            'yesNo' => $yesNo,
            'countryName' => $countryName,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $cabotageOnly->getRepresentation()
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

<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\EmissionsStandards;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * EmissionsStandardsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardsTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetRepresentation
     */
    public function testGetRepresentation($yesNo)
    {
        $emissionsStandards = new EmissionsStandards($yesNo);

        $expectedRepresentation = ['yesNo' => $yesNo];

        $this->assertEquals(
            $expectedRepresentation,
            $emissionsStandards->getRepresentation()
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

<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\IrhpApplication\BilateralCountryAccessible
 */

class BilateralCountryAccessibleTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $id = 25;
        $country = 'DE';

        $sut = BilateralCountryAccessible::create(
            [
                'id' => $id,
                'country' => $country,
            ]
        );
        $this->assertEquals($id, $sut->getId());
        $this->assertEquals($country, $sut->getCountry());
        $this->assertEquals(
            [
                'id' => $id,
                'country' => $country,
            ],
            $sut->getArrayCopy()
        );
    }
}

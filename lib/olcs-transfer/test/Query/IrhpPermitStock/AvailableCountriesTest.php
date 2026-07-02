<?php

namespace Dvsa\OlcsTest\Transfer\Query\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\AvailableCountries;

/**
 * Available Countries test
 */
class AvailableCountriesTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $sut = AvailableCountries::create([]);

        $this->assertEquals(
            [],
            $sut->getArrayCopy()
        );
    }
}

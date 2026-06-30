<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\EmissionsByYear;

/**
 * Emissions By Year test
 */
class EmissionsByYearTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = EmissionsByYear::create(['irhpPermitType' => 1, 'year' => 3000]);

        $this->assertEquals(1, $query->getIrhpPermitType());
        $this->assertEquals(3000, $query->getYear());
    }
}

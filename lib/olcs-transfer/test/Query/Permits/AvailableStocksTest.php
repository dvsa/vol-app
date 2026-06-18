<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableStocks;

/**
 * AvailableStocks test
 */
class AvailableStocksTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = AvailableStocks::create(['irhpPermitType' => 1, 'year' => 2020]);

        $this->assertEquals(1, $query->getIrhpPermitType());
        $this->assertEquals(2020, $query->getYear());
    }
}

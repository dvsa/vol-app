<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\StockAlignmentReport;

/**
 * StockAlignmentReport test
 */
class StockAlignmentReportTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = StockAlignmentReport::create(['id' => 67]);

        $this->assertEquals(67, $query->getId());
    }
}

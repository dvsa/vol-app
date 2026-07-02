<?php

namespace Dvsa\OlcsTest\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\Permits\AvailableYears;

/**
 * Available Years test
 */
class AvailableYearsTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure()
    {
        $query = AvailableYears::create(['type' => 1]);

        $this->assertEquals(1, $query->getType());
    }
}
